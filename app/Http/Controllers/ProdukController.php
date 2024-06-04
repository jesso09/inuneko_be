<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PetShop;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ProdukController extends Controller
{
    public function index()
    {
        $produkData = Produk::with('shop')->latest()->get();
        if (is_null($produkData)) {
            return response([
                'message' => 'Content not found',
                'data' => $produkData
            ], 404);
        }
        return response([
            'message' => 'Data Produk',
            'data' => $produkData
        ], 200);
    }

    /**
     * store
     *
     * @param Request $request
     */

    public function store(Request $request)
    {
        if (Auth::user()->role != "Pet Shop") {
            return response([
                'message' => 'You cannot crate this model',
            ], 400);
        }

        $id_shop = Auth::user()->petShop->id;
        $productPict = null;

        $newData = $request->all();
        //Validasi Formulir
        $validator = Validator::make($newData, [
            'produk_pict' => 'mimes:jpeg,png,jpg,gif|max:50000',
            'nama' => 'required',
            'kategori' => 'required',
            'harga' => 'required',
            'desc' => 'required',
            'stok' => 'required',
            // 'status' => 'required',
        ], [
            'photo.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif.',
        ]);
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }

        // Simpan gambar dalam direktori 'storage/app/public/images'
        if ($request->produk_pict != null) {
            $path = $request->file('produk_pict')->store('public/images');
            $productPict = basename($path);
        }

        $newPetData = Produk::create([
            'shop_id' => $id_shop,
            'produk_pict' => $productPict,
            'nama' => $request->nama,
            'kategori' => $request->kategori,
            'harga' => $request->harga,
            'desc' => $request->desc,
            'stok' => $request->stok,
        ]);

        return response([
            'message' => 'Data added successfully',
            'data' => $newPetData
        ], 201);
    }

    public function show($id)
    {
        $dataFound = Produk::with('shop')->find($id);

        if (is_null($dataFound)) {
            return response([
                'message' => 'Content not found',
                'data' => null
            ], 404);
        }

        return response([
            'message' => 'Successfully',
            'data' => $dataFound
        ], 200);
    }
    
    public function showToko($id)
    {
        $dataFound = PetShop::with('product.shop')->find($id);

        if (is_null($dataFound)) {
            return response([
                'message' => 'Content not found',
                'data' => null
            ], 404);
        }

        return response([
            'message' => 'Successfully',
            'data' => $dataFound
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
        $productFound = Produk::find($id);

        if (!$productFound) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Hapus file gambar jika ada
        if ($productFound->produk_pict) {
            Storage::delete('public/images/' . $productFound->produk_pict);
        }

        // Hapus konten dari database
        $productFound->delete();

        return response()->json([
            'message' => 'Pet deleted successfully',
            'data' => $productFound,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->role != "Pet Shop") {
            return response([
                'message' => 'You cannot edit this model',
            ], 400);
        }

        $id_shop = Auth::user()->petShop->id;

        $targetProduk = Produk::find($id);

        if (is_null($targetProduk)) {
            return response([
                'message' => 'Data not found',
                'data' => null
            ], 404);
        }

        $update = $request->all();

        $validator = Validator::make($update, [
            'produk_pict' => 'mimes:jpeg,png,jpg,gif|max:50000',
            'nama' => 'required',
            'kategori' => 'required',
            'harga' => 'required',
            'desc' => 'required',
            'stok' => 'required',
        ], [
            'produk_pict.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif.',
        ]);

        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }

        $targetProduk->shop_id = $id_shop;
        $targetProduk->nama = $update['nama'];
        $targetProduk->kategori = $update['kategori'];
        $targetProduk->harga = $update['harga'];
        $targetProduk->desc = $update['desc'];
        $targetProduk->stok = $update['stok'];

        if ($request->produk_pict == null) {
            if ($targetProduk->save()) {
                return response([
                    'message' => 'Data Updated Success',
                    'data' => $targetProduk
                ], 200);
            }
        } else if ($request->produk_pict != null) {
            Storage::delete('public/images/' . $targetProduk->produk_pict);
            $targetProduk->produk_pict = null;
        }

        if ($request->produk_pict != null && $targetProduk->produk_pict == null) {
            $path = $request->file('produk_pict')->store('public/images');
            $petPict = basename($path);
            $targetProduk->produk_pict = $petPict;
        } else if ($request->produk_pict != null && $targetProduk->produk_pict != null) {
            Storage::delete('public/images/' . $targetProduk->produk_pict);
            $path = $request->file('produk_pict')->store('public/images');
            $petPict = basename($path);
            $targetProduk->produk_pict = $petPict;
        }

        if ($targetProduk->save()) {
            return response([
                'message' => 'Data Updated Success',
                'data' => $targetProduk
            ], 200);
        }

        return response([
            'message' => 'Failed to update data',
            'data' => null
        ], 400);
    }

    public function getProductImage($fileName)
    {
        $path = storage_path('app/public/images/' . $fileName);

        if (!File::exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
}
