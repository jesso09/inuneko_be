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
    public function petShopIndex()
    {
        $petShopData = PetShop::latest()->get();
        if (is_null($petShopData)) {
            return response([
                'message' => 'Content not found',
                'data' => $petShopData
            ], 404);
        }
        return response([
            'message' => 'Data Vet',
            'data' => $petShopData
        ], 200);
    }

    public function index()
    {
        $role = Auth::user()->role;
        if ($role == "Pet Shop") {
            $idPetShop = Auth::user()->petShop->id;
            $produkData = Produk::where('shop_id', $idPetShop)->with('shop')->latest()->get();
            return response([
                'message' => 'Data Produk',
                'data' => $produkData
            ], 200);
        }

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
            'produk_pict.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif.',
        ]);
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }



        // Simpan gambar dalam direktori 'storage/app/public/images'
        if ($request->produk_pict != null) {

            $original_name = $request->produk_pict->getClientOriginalName();
            $generated_name = 'produk' . '-' . time() . '.' . $request->produk_pict->extension();

            // menyimpan gambar
            $request->produk_pict->storeAs('public/produk', $generated_name);
        }else {
            $generated_name = null;
        }

        $newData = Produk::create([
            'shop_id' => $id_shop,
            'produk_pict' => $generated_name,
            'nama' => $request->nama,
            'kategori' => $request->kategori,
            'harga' => $request->harga,
            'desc' => $request->desc,
            'stok' => $request->stok,
            'status' => "Dipublikasi",
        ]);

        return response([
            'message' => 'Data added successfully',
            'data' => $newData
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
    
    public function infoStock($id)
    {
        $dataFound = Produk::find($id);

        if (is_null($dataFound)) {
            return response([
                'message' => 'Content not found',
                'data' => null
            ], 404);
        }

        return response([
            'message' => 'Successfully',
            'data' => $dataFound->stok,
        ], 200);
    }
    
    public function hideProduk($id)
    {
        $dataFound = Produk::find($id);

        if (is_null($dataFound)) {
            return response([
                'message' => 'Content not found',
                'data' => null
            ], 404);
        }

        if ($dataFound->status == "Dipublikasi") {
            $dataFound->status = "Disembunyikan";
            $dataFound->save();
        }else if ($dataFound->status == "Disembunyikan") {
            $dataFound->status = "Dipublikasi";
            $dataFound->save();
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
            unlink(public_path('storage/produk/' . $productFound->produk_pict));
        }

        // Hapus konten dari database
        $productFound->delete();

        return response()->json([
            'message' => 'Produk deleted successfully',
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
            if ($targetProduk->produk_pict == null) {
                $original_name = $request->produk_pict->getClientOriginalName();
                $generated_name = 'produk' . '-' . time() . '.' . $request->produk_pict->extension();

                // menyimpan gambar
                $request->produk_pict->storeAs('public/produk', $generated_name);
                $targetProduk->produk_pict = $generated_name;

            } else if ($targetProduk->produk_pict != null) {
                unlink(public_path('storage/produk/' . $targetProduk->produk_pict));

                $original_name = $request->produk_pict->getClientOriginalName();
                $generated_name = 'produk' . '-' . time() . '.' . $request->produk_pict->extension();
                // menyimpan gambar
                $request->produk_pict->storeAs('public/produk', $generated_name);
                $targetProduk->produk_pict = $generated_name;
            }
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
