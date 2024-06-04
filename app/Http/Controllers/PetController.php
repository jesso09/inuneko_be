<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class PetController extends Controller
{
    public function index()
    {
        $id_cust = Auth::user()->customer->id;
        $petData = Pet::where('customer_id', $id_cust)->latest()->get();
        if (is_null($petData)) {
            return response([
                'message' => 'Data not found',
                'data' => $petData
            ], 404);
        }
        return response([
            'message' => 'Data Pet',
            'data' => $petData
        ], 200);
    }

    /**
     * store
     *
     * @param Request $request
     */

    public function store(Request $request)
    {
        if (Auth::user()->role != "Customer") {
            return response([
                'message' => 'You cannot crate this model',
            ], 400);
        }

        $id_customer = Auth::user()->customer->id;
        $petPict = null;

        $newData = $request->all();
        //Validasi Formulir
        $validator = Validator::make($newData, [
            'pet_pict' => 'mimes:jpeg,png,jpg,gif|max:50000',
            'nama' => 'required',
            'gender' => 'required',
            'jenis' => 'required',
            'ras' => 'required',
            'umur' => 'required',
            // 'status' => 'required',
        ], [
            'pet_pict.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif.',
        ]);
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }

        // Simpan gambar dalam direktori 'storage/app/public/images'
        if ($request->pet_pict != null) {
            $path = $request->file('pet_pict')->store('public/images');
            $petPict = basename($path);
        }

        $newPetData = Pet::create([
            'customer_id' => $id_customer,
            'pet_pict' => $petPict,
            'nama' => $request->nama,
            'gender' => $request->gender,
            'jenis' => $request->jenis,
            'ras' => $request->ras,
            'umur' => $request->umur,
            'status' => $request->status,
        ]);

        return response([
            'message' => 'Data added successfully',
            'data' => $newPetData
        ], 201);
    }

    public function getPet($id)
    {
        $petFound = Pet::with('cust')->find($id);

        if (is_null($petFound)) {
            return response([
                'message' => 'Data not found',
                'data' => null
            ], 404);
        }

        return response([
            'message' => 'Successfully',
            // 'photo' => $petFound->pet_pict,
            'data' => $petFound
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $petFound = Pet::find($id);

        if (!$petFound) {
            return response()->json(['message' => 'Pet not found'], 404);
        }

        // Hapus file gambar jika ada
        if ($petFound->pet_pict) {
            Storage::delete('public/images/' . $petFound->pet_pict);
        }

        // Hapus konten dari database
        $petFound->delete();

        return response()->json([
            'message' => 'Pet deleted successfully',
            'data' => $petFound,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->role != "Customer") {
            return response([
                'message' => 'You cannot edit this model',
            ], 400);
        }

        $id_customer = Auth::user()->customer->id;

        $targetPet = Pet::find($id);

        if (is_null($targetPet)) {
            return response([
                'message' => 'Data not found',
                'data' => null
            ], 404);
        }

        $update = $request->all();
        // return response([
        //     'message' => 'Update',
        //     'data' => $update
        // ], 200);

        $validator = Validator::make($update, [
            'pet_pict' => 'mimes:jpeg,png,jpg,gif|max:50000',
            'nama' => 'required',
            'gender' => 'required',
            'jenis' => 'required',
            'ras' => 'required',
            'umur' => 'required',
            // 'status' => 'required',
        ], [
            'pet_pict.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif.',
        ]);

        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }

        $targetPet->customer_id = $id_customer;
        // $targetPet->pet_pict = $update['pet_pict'];
        $targetPet->nama = $update['nama'];
        $targetPet->gender = $update['gender'];
        $targetPet->jenis = $update['jenis'];
        $targetPet->ras = $update['ras'];
        $targetPet->umur = $update['umur'];
        // $targetPet->status = $update['status'];

        if ($request->pet_pict == null) {
            if ($targetPet->save()) {
                return response([
                    'message' => 'Data Updated Success',
                    'data' => $targetPet
                ], 200);
            }
        } else if ($request->pet_pict != null) {
            Storage::delete('public/images/' . $targetPet->pet_pict);
            $targetPet->pet_pict = null;
        }

        if ($request->pet_pict != null && $targetPet->pet_pict == null) {
            $path = $request->file('pet_pict')->store('public/images');
            $petPict = basename($path);
            $targetPet->pet_pict = $petPict;
        } else if ($request->pet_pict != null && $targetPet->pet_pict != null) {
            Storage::delete('public/images/' . $targetPet->pet_pict);
            $path = $request->file('pet_pict')->store('public/images');
            $petPict = basename($path);
            $targetPet->pet_pict = $petPict;
        }

        if ($targetPet->save()) {
            return response([
                'message' => 'Data Updated Success',
                'data' => $targetPet
            ], 200);
        }

        return response([
            'message' => 'Failed to update data',
            'data' => null
        ], 400);
    }

    public function getPetImage($fileName)
    {
        $path = storage_path('app/public/images/' . $fileName);

        if (!File::exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
}