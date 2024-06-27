<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Penitipan;
use App\Models\Pet;
use App\Models\PetShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PenitipanController extends Controller
{
    public function index()
    {
        $role = Auth::user()->role;
        if ($role == "Customer") {
            $idCustomer = Auth::user()->customer->id;
            $penitipanData = Penitipan::where('customer_id', $idCustomer)->with('petShop', 'pet')->latest()->get();

            if (is_null($penitipanData)) {
                return response([
                    'message' => 'Data not found',
                    'data' => $penitipanData
                ], 404);
            }
            return response([
                'message' => 'Data Penitipan',
                'data' => $penitipanData
            ], 200);
        }
        if ($role == "Pet Shop") {
            $idPetShop = Auth::user()->petShop->id;
            $penitipanData = Penitipan::where('pet_shop_id', $idPetShop)->with('cust', 'pet')->latest()->get();

            if (is_null($penitipanData)) {
                return response([
                    'message' => 'Data not found',
                    'data' => $penitipanData
                ], 404);
            }
            return response([
                'message' => 'Data Penitipan',
                'data' => $penitipanData
            ], 200);
        }
    }

    public function show($id)
    {
        $role = Auth::user()->role;
        if ($role == "Customer") {
            $penitipanData = Penitipan::with('petShop', 'pet')->find($id);

            if (is_null($penitipanData)) {
                return response([
                    'message' => 'Data not found',
                    'data' => $penitipanData
                ], 404);
            }
            return response([
                'message' => 'Data Penitipan',
                'data' => $penitipanData
            ], 200);
        }
        if ($role == "Pet Shop") {
            $penitipanData = Penitipan::with('cust', 'pet')->find($id);

            if (is_null($penitipanData)) {
                return response([
                    'message' => 'Data not found',
                    'data' => $penitipanData
                ], 404);
            }
            return response([
                'message' => 'Data Penitipan',
                'data' => $penitipanData
            ], 200);
        }
    }

    public function store(Request $request)
    {
        $idCustomer = Auth::user()->customer->id;

        // Validasi Formulir
        $validator = Validator::make($request->all(), [
            'pet_id' => 'required',
            'pet_shop_id' => 'required',
            'penitipan_order_id' => 'required',
            'durasi' => 'required',
            'harga' => 'required',
            'mulai' => 'required',
            'selesai' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['message' => 'Invalid input data', 'errors' => $validator->errors()], 400);
        }

        $newData = Penitipan::create([
            'customer_id' => $idCustomer,
            'pet_id' => $request->pet_id,
            'pet_shop_id' => $request->pet_shop_id,
            'penitipan_order_id' => $request->penitipan_order_id,
            'durasi' => $request->durasi,
            'harga' => $request->harga,
            'mulai' => $request->mulai,
            'selesai' => $request->selesai,
            'status' => "Menunggu",
        ]);

        $petShop = PetShop::find($request->pet_shop_id);
        $petShop->kapasitas_penitipan = $petShop->kapasitas_penitipan - 1;
        $petShop->save();

        $pet = Pet::find($request->pet_id);
        $pet->status = "Menunggu Penitipan";
        $pet->save();

        return response([
            'message' => 'Data added successfully',
            'data' => $newData,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $idCustomer = Auth::user()->customer->id;
        $dataPenitipan = Penitipan::find($id);

        if (is_null($dataPenitipan)) {
            return response([
                'message' => 'Data not found',
                'data' => null
            ], 404);
        }

        $update = $request->all();
        $validator = Validator::make($update, [
            'pet_id' => 'required',
            'pet_shop_id' => 'required',
            'durasi' => 'required',
            'harga' => 'required',
            'mulai' => 'required',
            'selesai' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }

        $dataPenitipan->customer_id = $idCustomer;
        $dataPenitipan->pet_id = $update['pet_id'];
        $dataPenitipan->pet_shop_id = $update['pet_shop_id'];
        $dataPenitipan->durasi = $update['durasi'];
        $dataPenitipan->harga = $update['harga'];
        $dataPenitipan->mulai = $update['mulai'];
        $dataPenitipan->selesai = $update['selesai'];

        if ($dataPenitipan->save()) {
            return response([
                'message' => 'Data Updated Success',
                'data' => $dataPenitipan
            ], 200);
        }

        return response([
            'message' => 'Failed to update data',
            'data' => null
        ], 400);
    }

    public function changeStatus(Request $request, $id)
    {
        $dataFound = Penitipan::find($id);

        if (!$dataFound) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $dataFound->status = $request->status;
        $dataFound->save();

        if ($request->status == "Pet Diterima") {
            $dataFound->pet->status = "Dititipkan";
            $dataFound->petShop->kapasitas_penitipan -= 1;
            $dataFound->petShop->save();
            $dataFound->pet->save();
        }
        
        if ($request->status == "Selesai") {
            $dataFound->pet->status = "Dikembalikan";
            $dataFound->petShop->kapasitas_penitipan += 1;
            $dataFound->petShop->save();
            $dataFound->pet->save();
        }
        
        if ($request->status == "Dinilai") {
            $dataFound->pet->status = null;
            $dataFound->pet->save();
        }

        return response()->json([
            'message' => 'Status Changed',
            'data' => $dataFound->pet->status,
        ], 200);
    }
}
