<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LayananVet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LayananController extends Controller
{
    public function index()
    {
        $id_vet = Auth::user()->vet->id;
        $serviceData = LayananVet::where('vet_id', $id_vet)->latest()->get();
        if (is_null($serviceData)) {
            return response([
                'message' => 'Data not found',
                'data' => $serviceData
            ], 404);
        }
        return response([
            'message' => 'Data Service',
            'data' => $serviceData
        ], 200);
    }

    public function indexById($id)
    {
        $serviceData = LayananVet::where('vet_id', $id)->latest()->get();
        if (is_null($serviceData)) {
            return response([
                'message' => 'Data not found',
                'data' => $serviceData
            ], 404);
        }
        return response([
            'message' => 'Data Service',
            'data' => $serviceData
        ], 200);
    }

    public function show($id)
    {
        $serviceData = LayananVet::find($id);
        if (is_null($serviceData)) {
            return response([
                'message' => 'Data not found',
                'data' => $serviceData
            ], 404);
        }
        return response([
            'message' => 'Data Service',
            'data' => $serviceData
        ], 200);
    }

    public function store(Request $request)
    {
        $id_vet = Auth::user()->vet->id;

        // Validasi Formulir
        $validator = Validator::make($request->all(), [
            'nama_layanan' => 'required',
            'harga' => 'required',
            'harga_per_jarak' => 'required',
            'keterangan' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['message' => 'Invalid input data', 'errors' => $validator->errors()], 400);
        }

        $newData = LayananVet::create([
            'vet_id' => $id_vet,
            'nama_layanan' => $request->nama_layanan,
            'harga' => $request->harga,
            'harga_per_jarak' => $request->harga_per_jarak,
            'keterangan' => $request->keterangan,
        ]);

        return response([
            'message' => 'Data added successfully',
            'data' => $newData
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $id_vet = Auth::user()->vet->id;
        $dataService = LayananVet::find($id);

        if (is_null($dataService)) {
            return response([
                'message' => 'Data not found',
                'data' => null
            ], 404);
        }

        $update = $request->all();
        $validator = Validator::make($update, [
            'nama_layanan' => 'required',
            'harga' => 'required',
            'harga_per_jarak' => 'required',
            'keterangan' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }

        $dataService->vet_id = $id_vet;
        $dataService->nama_layanan = $update['nama_layanan'];
        $dataService->harga = $update['harga'];
        $dataService->harga_per_jarak = $update['harga_per_jarak'];
        $dataService->keterangan = $update['keterangan'];

        if ($dataService->save()) {
            return response([
                'message' => 'Data Updated Success',
                'data' => $dataService
            ], 200);
        }

        return response([
            'message' => 'Failed to update data',
            'data' => null
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $dataFound = LayananVet::find($id);

        if (!$dataFound) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $dataFound->delete();

        return response()->json([
            'message' => 'Data deleted successfully',
            'data' => $dataFound,
        ], 200);
    }
}
