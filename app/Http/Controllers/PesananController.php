<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DetailPesanan;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Midtrans\Config;
use Midtrans\Snap;

class PesananController extends Controller
{
    public function index()
    {
        $idCustomer = Auth::user()->customer->id;
        $orderData = Pesanan::where('customer_id', $idCustomer)->with('cust', 'detailOrder.product.shop')->latest()->get();

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

    public function indexShop()
    {
        $idShop = Auth::user()->petShop->id;

        $orderData = Pesanan::whereHas('detailOrder.product.shop', function ($query) use ($idShop) {
            $query->where('id', $idShop);
        })
            ->with([
                'detailOrder' => function ($query) use ($idShop) {
                    $query->whereHas('product.shop', function ($query) use ($idShop) {
                        $query->where('id', $idShop);
                    });
                },
                'detailOrder.product.shop',
                'cust'
            ])
            ->latest()
            ->get();

        if ($orderData->isEmpty()) {
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

    public function show($id, $idShop)
    {
        $dataFound = DetailPesanan::where('order_id', $id)
            ->whereHas('product', function ($query) use ($idShop) {
                $query->where('shop_id', $idShop);
            })
            ->with('product.shop')
            ->latest()
            ->get();

        if (!$dataFound) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'message' => 'Status changed successfully',
            'data' => $dataFound,
        ], 200);
    }

    public function store(Request $request)
    {
        $idCustomer = Auth::user()->customer->id;

        // Validasi Formulir
        $validator = Validator::make($request->all(), [
            // 'shop_id' => 'required',
            'no_pesanan' => 'required',
            'tanggal_pesan' => 'required',
            'alamat_pengiriman' => 'required',

            'detail_orders' => 'required|array',
            'detail_orders.*.produk_id' => 'required',
            'detail_orders.*.jumlah_pesan' => 'required',
            'detail_orders.*.total_harga' => 'required',
            'detail_orders.*.status' => 'required',
        ]);
        if ($validator->fails()) {
            return response(['message' => 'Invalid input data', 'errors' => $validator->errors()], 400);
        }

        $newData = Pesanan::create([
            'customer_id' => $idCustomer,
            // 'shop_id' => $request->shop_id,
            'no_pesanan' => $request->no_pesanan,
            'tanggal_pesan' => $request->tanggal_pesan,
            'alamat_pengiriman' => $request->alamat_pengiriman,
        ]);

        foreach ($request->detail_orders as $detail) {
            $newData->detailOrder()->create([
                'produk_id' => $detail['produk_id'],
                'jumlah_pesan' => $detail['jumlah_pesan'],
                'total_harga' => $detail['total_harga'],
                'status' => $detail['status'],
            ]);
        }

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
        $shopId = $request->shop_id;

        $dataFound = DetailPesanan::where('order_id', $id)
            ->whereHas('product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
            ->latest()
            ->get();

        if (!$dataFound) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        foreach ($dataFound as $detail) {
            $detail->status = $request->status;
            $detail->save();
        }

        if ($request->status == "Dibayar") {
            foreach ($dataFound as $detail) {
                $detail->product->stok -= $detail->jumlah_pesan;
                $detail->product->save();
            }
        }

        return response()->json([
            'message' => 'Status changed successfully',
            'data' => $dataFound,
        ], 200);
    }

    public function __construct()
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        // Config::$isProduction = config('midtrans.is_production');
    }

    public function createTransaction(Request $request)
    {
        // $orderId = uniqid();
        $params = [
            'transaction_details' => [
                'order_id' => $request->order_id,
                'gross_amount' => $request->amount,
            ],
            'customer_details' => [
                'first_name' => $request->first_name,
                // 'last_name' => $request->last_name,
                'email' => $request->email,
                'address' => $request->address,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return response()->json([
                'snap_token' => $snapToken,
                // 'order_id' => $orderId,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
