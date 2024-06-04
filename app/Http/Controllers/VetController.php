<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Vet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VetController extends Controller
{
    public function index()
    {
        $vetData = Vet::latest()->get();
        if (is_null($vetData)) {
            return response([
                'message' => 'Content not found',
                'data' => $vetData
            ], 404);
        }
        return response([
            'message' => 'Data Vet',
            'data' => $vetData
        ], 200);
    }
}
