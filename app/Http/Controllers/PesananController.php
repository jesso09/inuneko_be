<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PesananController extends Controller
{
    public function index()
    {
        $idCustomer = Auth::user()->customer->id;
        $orderData = Pesanan::where('customer_id', $idCustomer)->latest()->get();

        if (is_null($orderData)) {
            return response([
                'message' => 'Data not found',
                'data' => $orderData
            ], 404);
        }
        return response([
            'message' => 'Data Order',
            'data' => $orderData
        ], 200);
    }

    public function show($id)
    {
        $orderData = Pesanan::find($id);
        if (is_null($orderData)) {
            return response([
                'message' => 'Data not found',
                'data' => $orderData
            ], 404);
        }
        return response([
            'message' => 'Data Order',
            'data' => $orderData
        ], 200);
    }

    public function store(Request $request)
    {
        $idCustomer = Auth::user()->customer->id;

        // Validasi Formulir
        $validator = Validator::make($request->all(), [
            'produk_id' => 'required',
            'no_pesanan' => 'required',
            'jumlah_pesan' => 'required',
            'tanggal_pesan' => 'required',
        ]);
        if ($validator->fails()) {
            return response(['message' => 'Invalid input data', 'errors' => $validator->errors()], 400);
        }

        $newData = Pesanan::create([
            'customer_id' => $idCustomer,
            'produk_id' => $request->produk_id,
            'no_pesanan' => $request->no_pesanan,
            'jumlah_pesan' => $request->jumlah_pesan,
            'tanggal_pesan' => $request->tanggal_pesan,
            'status' => "Diproses",
        ]);

        return response([
            'message' => 'Data added successfully',
            'data' => $newData
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $idCustomer = Auth::user()->customer->id;
        $dataOrder = Pesanan::find($id);

        if (is_null($dataOrder)) {
            return response([
                'message' => 'Data not found',
                'data' => null
            ], 404);
        }

        $update = $request->all();
        $validator = Validator::make($update, [
            'produk_id' => 'required',
            'no_pesanan' => 'required',
            'jumlah_pesan' => 'required',
            'tanggal_pesan' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }

        $dataOrder->customer_id = $idCustomer;
        $dataOrder->produk_id = $update['produk_id'];
        $dataOrder->no_pesanan = $update['no_pesanan'];
        $dataOrder->jumlah_pesan = $update['jumlah_pesan'];
        $dataOrder->tanggal_pesan = $update['tanggal_pesan'];

        if ($dataOrder->save()) {
            return response([
                'message' => 'Data Updated Success',
                'data' => $dataOrder
            ], 200);
        }

        return response([
            'message' => 'Failed to update data',
            'data' => null
        ], 400);
    }

    public function changeStatus(Request $request, $id)
    {
        $dataFound = Pesanan::find($id);

        if (!$dataFound) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $dataFound->status = $request->status;
        $dataFound->save();

        return response()->json([
            'message' => 'Data deleted successfully',
            'data' => $dataFound,
        ], 200);
    }
}
