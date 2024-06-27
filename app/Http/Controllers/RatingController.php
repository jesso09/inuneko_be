<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = Rating::where('receiver_id', $user->id)->latest()->get();
        if (is_null($data)) {
            return response([
                'message' => 'Content not found',
                'data' => $data
            ], 404);
        }
        return response([
            'message' => 'Data Rate',
            'data' => $data
        ], 200);
    }

    public function store(Request $request, $id)
    {
        $sender = Auth::user();
        $receiver = User::find($id);

        if ($sender->id == $receiver->id) {
            return response([
                'message' => "Can't Rate Yourself",
                'data' => null
            ], 400);
        }

        // Validasi Formulir
        $validator = Validator::make($request->all(), [
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['message' => 'Invalid input data', 'errors' => $validator->errors()], 400);
        }

        $newData = Rating::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'value' => $request->value,
            'desc' => $request->desc,
        ]);

        $role = $receiver->role;
        $totalRateValue = 0;

        if ($role == "Vet") {
            $target = $receiver->vet;
            $rateForTarget = Rating::where("receiver_id", $target->user_id)->get();
            foreach ($rateForTarget as $value) {
                $totalRateValue += $value->value;
            }
            $newRate = $totalRateValue / $rateForTarget->count();
            $receiver->vet->rating = $newRate;
            $receiver->vet->save();
            if ($receiver->vet->save()) {
                return response([
                    'message' => 'Data added successfully',
                    'data' => $newRate
                ], 201);
            }
        } else if ($role == "Pet Shop") {
            $target = $receiver->petShop;
            $rateForTarget = Rating::where("receiver_id", $target->user_id)->get();
            foreach ($rateForTarget as $value) {
                $totalRateValue += $value->value;
            }
            $newRate = $totalRateValue / $rateForTarget->count();
            $receiver->petShop->rating = $newRate;
            $receiver->petShop->save();
            if ($receiver->petShop->save()) {
                return response([
                    'message' => 'Data added successfully',
                    'data' => $newRate
                ], 201);
            }
        }

        return response([
            'message' => 'Failed to give rate',
            'data' => $newData
        ], 400);
    }
}
