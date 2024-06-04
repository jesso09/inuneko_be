<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\HouseCall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HouseCallController extends Controller
{
    public function index()
    {
        $role = Auth::user()->role;
        if ($role == "Customer") {
            $idCustomer = Auth::user()->customer->id;
            $houseCallData = HouseCall::where('customer_id', $idCustomer)->latest()->get();

            if (is_null($houseCallData)) {
                return response([
                    'message' => 'Data not found',
                    'data' => $houseCallData
                ], 404);
            }
            return response([
                'message' => 'Data House Call',
                'data' => $houseCallData
            ], 200);
        }
        if ($role == "Vet") {
            $idVet = Auth::user()->vet->id;
            $houseCallData = HouseCall::where('vet_id', $idVet)->latest()->get();

            if (is_null($houseCallData)) {
                return response([
                    'message' => 'Data not found',
                    'data' => $houseCallData
                ], 404);
            }
            return response([
                'message' => 'Data House Call',
                'data' => $houseCallData
            ], 200);
        }
    }

    public function show($id)
    {
        $houseCallData = HouseCall::find($id);
        if (is_null($houseCallData)) {
            return response([
                'message' => 'Data not found',
                'data' => $houseCallData
            ], 404);
        }
        return response([
            'message' => 'Data House Call',
            'data' => $houseCallData
        ], 200);
    }

    public function store(Request $request)
    {
        $idCustomer = Auth::user()->customer->id;

        // Validasi Formulir
        $validator = Validator::make($request->all(), [
            'vet_id' => 'required',
            'service_id' => 'required',
            'mulai' => 'required',
            'selesai' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['message' => 'Invalid input data', 'errors' => $validator->errors()], 400);
        }

        $newData = HouseCall::create([
            'customer_id' => $idCustomer,
            'vet_id' => $request->vet_id,
            'service_id' => $request->service_id,
            'status' => "Ditunda",
            'mulai' => $request->mulai,
            'selesai' => $request->selesai,
        ]);

        return response([
            'message' => 'Data added successfully',
            'data' => $newData
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $idCustomer = Auth::user()->customer->id;
        $dataHouseCall = HouseCall::find($id);

        if (is_null($dataHouseCall)) {
            return response([
                'message' => 'Data not found',
                'data' => null
            ], 404);
        }

        $update = $request->all();
        $validator = Validator::make($update, [
            'vet_id' => 'required',
            'mulai' => 'required',
            'selesai' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }

        $dataHouseCall->customer_id = $idCustomer;
        $dataHouseCall->vet_id = $update['vet_id'];
        $dataHouseCall->service_id = $update['service_id'];
        // $dataHouseCall->status = $update['status'];
        $dataHouseCall->mulai = $update['mulai'];
        $dataHouseCall->selesai = $update['selesai'];

        if ($dataHouseCall->save()) {
            return response([
                'message' => 'Data Updated Success',
                'data' => $dataHouseCall
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
        $dataFound = HouseCall::find($id);

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
