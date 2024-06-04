<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AktivitasPenitipan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class AktivitasPenitipanController extends Controller
{
    public function index($id)
    {
        $activityData = AktivitasPenitipan::where('penitipan_id', $id)->latest()->get();
        if (is_null($activityData)) {
            return response([
                'message' => 'Aktivity not found',
                'data' => $activityData
            ], 404);
        }

        return response([
            'message' => 'Successfully',
            'data' => $activityData
        ], 200);
    }

    public function store(Request $request)
    {
        $newData = $request->all();
        //Validasi Formulir
        $validator = Validator::make($newData, [
            'penitipan_id' => 'required',
            'foto' => 'mimes:jpeg,png,jpg,gif|max:50000',
            'video' => 'mimes:avi,mp4,mkv,mov',
            'judul_aktivitas' => 'required',
            'waktu_aktivitas' => 'required',
            'keterangan' => 'required',
        ], [
            'foto.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif.',
            'video.mimes' => 'Format video yang diperbolehkan: avi, mp4, mkv.',
        ]);
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }

        $activityPict = null;
        $activityVid = null;
        // Simpan gambar dalam direktori 'storage/app/public/images'
        if ($request->foto != null && $request->video != null) {
            return response([
                'message' => 'Only 1 foto or 1 video is required',
                'data' => null
            ], 400);
        } else if ($request->foto != null) {
            $path = $request->file('foto')->store('public/images');
            $activityPict = basename($path);
        } else if ($request->video != null) {
            $vidPath = $request->file('video')->store('public/videos');
            $activityVid = basename($vidPath);
        }

        // Buat data baru dalam tabel anggota_keluarga
        $newActivity = AktivitasPenitipan::create([
            'penitipan_id' => $request->penitipan_id,
            'foto' => $activityPict,
            'video' => $activityVid,
            'judul_aktivitas' => $request->judul_aktivitas,
            'waktu_aktivitas' => $request->waktu_aktivitas,
            'keterangan' => $request->keterangan,
        ]);
        return response([
            'message' => 'Data added successfully',
            'data' => $newActivity
        ], 201);
    }

    public function show($id)
    {
        $dataFound = AktivitasPenitipan::find($id);

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
        $activityTarget = AktivitasPenitipan::find($id);

        if (!$activityTarget) {
            return response()->json(['message' => 'Activity not found'], 404);
        }

        // Hapus file gambar jika ada
        if ($activityTarget->foto) {
            Storage::delete('public/images/' . $activityTarget->foto);
        }

        // Hapus file video jika ada
        if ($activityTarget->video) {
            Storage::delete('public/videos/' . $activityTarget->video);
        }

        // Hapus konten dari database
        $activityTarget->delete();

        return response()->json([
            'message' => 'Activity deleted successfully',
            'data' => $activityTarget
        ]);
    }

    public function getVideoActivity($videoFileName)
    {
        $path = storage_path('app/public/videos/' . $videoFileName);

        if (!File::exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    public function getFotoActivity($fotoFileName)
    {
        $path = storage_path('app/public/images/' . $fotoFileName);

        if (!File::exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    public function update(Request $request, $id)
    {
        $targetData = AktivitasPenitipan::find($id);

        if (is_null($targetData)) {
            return response([
                'message' => 'Data not found',
                'data' => null
            ], 404);
        }

        $update = $request->all();

        $validator = Validator::make($update, [
            'penitipan_id' => 'required',
            'foto' => 'mimes:jpeg,png,jpg,gif|max:50000',
            'video' => 'mimes:avi,mp4,mkv,mov',
            'judul_aktivitas' => 'required',
            'waktu_aktivitas' => 'required',
            'keterangan' => 'required',
        ], [
            'foto.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif.',
            'video.mimes' => 'Format video yang diperbolehkan: avi, mp4, mkv.',
        ]);

        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }

        $targetData->penitipan_id = $update['penitipan_id'];
        $targetData->judul_aktivitas = $update['judul_aktivitas'];
        $targetData->waktu_aktivitas = $update['waktu_aktivitas'];
        $targetData->keterangan = $update['keterangan'];

        if ($request->foto == null && $request->video == null) {
            if ($targetData->save()) {
                return response([
                    'message' => 'Data Updated Success',
                    'data' => $targetData
                ], 200);
            }
        } else if ($request->foto != null || $request->video != null) {
            Storage::delete('public/images/' . $targetData->foto);
            Storage::delete('public/videos/' . $targetData->video);
            $targetData->foto = null;
            $targetData->video = null;
        }

        if ($request->foto != null && $targetData->foto == null) {
            $path = $request->file('foto')->store('public/images');
            $fileName = basename($path);
            $targetData->foto = $fileName;
        } else if ($request->foto != null && $targetData->foto != null) {
            Storage::delete('public/images/' . $targetData->foto);
            $path = $request->file('foto')->store('public/images');
            $fileName = basename($path);
            $targetData->foto = $fileName;
        }

        if ($request->video != null && $targetData->video == null) {
            $path = $request->file('video')->store('public/videos');
            $vidFileName = basename($path);
            $targetData->video = $vidFileName;
        } else if ($request->video != null && $targetData->video != null) {
            Storage::delete('public/videos/' . $targetData->video);
            $path = $request->file('video')->store('public/videos');
            $vidFileName = basename($path);
            $targetData->video = $vidFileName;
        }


        if ($targetData->save()) {
            return response([
                'message' => 'Data Updated Success',
                'data' => $targetData
            ], 200);
        }

        return response([
            'message' => 'Failed to update data',
            'data' => null
        ], 400);
    }
}
