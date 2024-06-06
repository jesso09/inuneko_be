<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\File;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $loginData = $request->all();
        $validate = Validator::make($loginData, [
            'email' => 'required',
            'password' => 'required',
            // 'fcm_token' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json(['message' => $validate->errors()->all()], 400);
        }

        if ($request->email == null) {
            if ($validate->fails()) {
                return response([
                    'status' => 401,
                    'message' => "Email/Username is Empty"
                ], 401);
            }
        } else if ($request->password == null) {
            if ($validate->fails()) {
                return response([
                    'status' => 401,
                    'message' => "Password is Empty"
                ], 401);
            }
        }

        $email = $request->email;
        $password = bcrypt($request->password);
        $user = User::where('email', '=', $email)->first();

        if (!Auth::attempt($loginData) && $request->fcm_token == null) {
            return response([
                'status' => 401,
                'message' => 'Wrong Email or Username',
            ], 401);
        } else {
            // $auth = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            $user = User::find($user->id);
            $user->fcm_token = $request->fcm_token;
            $user->save();
            return response([
                'role' => $user->role,
                'token' => $token,
                'fcm' => $user->fcm_token,
                'message' => 'Login Successfully',
            ]);
        }
    }

    /**
     * store
     *
     * @param Request $request
     * @return void
     */
    public function register(Request $request)
    {
        $registrationData = $request->all();
        //Validasi Formulir
        $validator = Validator::make($registrationData, [
            'role' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()], 400);
        }
        $registrationData['password'] = bcrypt($request->password);

        if ($request->role == "Customer") {
            $validator = Validator::make($registrationData, [
                'nama' => 'required',
                'alamat' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->all()], 400);
            }

            $userData = [
                'role' => $request->role,
                'email' => $request->email,
                'password' => $registrationData['password'],
            ];
            $user = User::create($userData);

            // create Customer
            $user->customer()->create([
                'nama' => $request->nama,
                'alamat' => $request->alamat,
            ]);

            // return
            return response()->json([
                'success' => true,
                'message' => 'Register as customer successfully',
            ], 200);
        } else if ($request->role == "Vet") {
            $validator = Validator::make($registrationData, [
                'nama' => 'required',
                'alamat' => 'required',
                'pengalaman' => 'required',
                // 'rating' => 'required',
                // 'info_lain' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->all()], 400);
            }

            $userData = [
                'role' => $request->role,
                'email' => $request->email,
                'password' => $registrationData['password'],
            ];
            $user = User::create($userData);

            // create Vet
            $user->vet()->create([
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'pengalaman' => $request->pengalaman,
                'rating' => 5,
                'info_lain' => $request->info_lain,
            ]);

            // return
            return response()->json([
                'success' => true,
                'message' => 'Register as vet successfully',
            ], 200);
        } else if ($request->role == "Pet Shop") {
            $validator = Validator::make($registrationData, [
                'nama' => 'required',
                'alamat' => 'required',
                'kapasitas_penitipan' => 'required',
                'harga_titip' => 'required',
                // 'info_lain' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->all()], 400);
            }

            $userData = [
                'role' => $request->role,
                'email' => $request->email,
                'password' => $registrationData['password'],
            ];
            $user = User::create($userData);

            // create Vet
            $user->petShop()->create([
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'kapasitas_penitipan' => $request->kapasitas_penitipan,
                'rating' => 5,
                'harga_titip' => $request->harga_titip,
            ]);

            // return
            return response()->json([
                'success' => true,
                'message' => 'Register as pet shop successfully',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Not eligible to register',
            ], 404);
        }

        // return response([
        //     'message' => 'Data added successfully',
        //     'paud' => $request->role,
        // ], 201);
    }

    /**
     * Logout a user (revoke the token).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        $user = Auth::user();
        $userLogout = User::find($user->id);

        $userLogout->fcm_token = null;
        $userLogout->save();

        // ~ Udah jalan emang kebaca error aja
        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out',
        ], 200);
    }

    public function getUser()
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'Customer':
                $data = $user->customer;

                // set id to customer_id
                $data->customer_id = $data->id;

                // unset id
                unset($data->id);

                $message = 'Profile Customer';
                break;

            case 'Vet':
                $data = $user->vet;

                // set id to vet
                $data->vet_id = $data->id;

                // unset id
                unset($data->id);

                $message = ' Profile Vet';
                break;

            case 'Pet Shop':
                $data = $user->petShop;

                // set id to petShop
                $data->petShop_id = $data->id;

                // unset id
                unset($data->id);

                $message = ' Profile Pet Shop';
                break;

            default:
                $data = null;
                $message = 'Unknown role';
                break;
        }

        return response()->json([
            'data' => $data,
            'email' => $user->email,
            'message' => $message,
            'role' => $user->role,
        ], 200);
    }

    public function getUserById($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'data' => $user,
                'message' => "User not found",
            ], 400);
        }
        return response()->json([
            'data' => $user,
        ], 200);
    }

    public function getPP($fileName)
    {
        $path = storage_path('app/public/images/' . $fileName);

        if (!File::exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }
}
