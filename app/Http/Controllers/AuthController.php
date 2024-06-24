<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PetShop;
use App\Models\Vet;
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
                // $data = $user->customer->with('customer');
                // $data->customer_id = $data->id;
                $message = 'Profile Customer';
                $data = User::with('vet', 'petShop', 'customer')->find($user->id);
                break;

            case 'Vet':
                // $data = $user->vet;
                // $data->vet_id = $data->id;
                $message = ' Profile Vet';
                $data = User::with('vet', 'petShop', 'customer')->find($user->id);
                break;

            case 'Pet Shop':
                $message = ' Profile Pet Shop';
                $data = User::with('vet', 'petShop', 'customer')->find($user->id);
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
        $user = User::with('vet', 'petShop', 'customer')->find($id);

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

    public function update(Request $request)
    {
        if (!Auth::user()) {
            return response([
                'message' => 'You cannot edit this model',
            ], 400);
        }

        $role = Auth::user()->role;
        $update = $request->all();

        if ($role == "Customer") {
            $targetCust = Customer::where('user_id', Auth::user()->id)->first();

            $validator = Validator::make($update, [
                'profile_pict' => 'mimes:jpeg,png,jpg,gif|max:50000',
                'nama' => 'required',
                'alamat' => 'required',
            ], [
                'profile_pict.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif.',
            ]);

            if ($validator->fails()) {
                return response(['message' => $validator->errors()], 400);
            }
            $targetCust->nama = $update['nama'];
            $targetCust->alamat = $update['alamat'];

            if ($request->profile_pict == null) {
                if ($targetCust->save()) {
                    return response([
                        'message' => 'Data Updated Success',
                        'data' => $targetCust
                    ], 200);
                }
            } else if ($request->profile_pict != null) {
                if ($targetCust->profile_pict == null) {
                    $original_name = $request->profile_pict->getClientOriginalName();
                    $generated_name = 'pp' . '-' . time() . '.' . $request->profile_pict->extension();

                    // menyimpan gambar
                    $request->profile_pict->storeAs('public/pp', $generated_name);
                    $targetCust->profile_pict = $generated_name;


                } else if ($targetCust->profile_pict != null) {

                    unlink(public_path('storage/public/pp/' . $targetCust->profile_pict));

                    $original_name = $request->profile_pict->getClientOriginalName();
                    $generated_name = 'pp' . '-' . time() . '.' . $request->profile_pict->extension();
                    // menyimpan gambar
                    $request->profile_pict->storeAs('public/pp', $generated_name);
                    $targetCust->profile_pict = $generated_name;
                }

                if ($targetCust->save()) {
                    return response([
                        'message' => 'Data Updated Success',
                        'data' => $targetCust
                    ], 200);
                }
            }
            return response([
                'message' => 'Failed to update data',
                'data' => null
            ], 400);
        }

        if ($role == "Vet") {
            $targetVet = Vet::where('user_id', Auth::user()->id)->first();

            $validator = Validator::make($update, [
                'profile_pict' => 'mimes:jpeg,png,jpg,gif|max:50000',
                'nama' => 'required',
                'alamat' => 'required',
                'pengalaman' => 'required',
            ], [
                'profile_pict.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif.',
            ]);

            if ($validator->fails()) {
                return response(['message' => $validator->errors()], 400);
            }
            $targetVet->nama = $update['nama'];
            $targetVet->alamat = $update['alamat'];
            $targetVet->pengalaman = $update['pengalaman'];
            $targetVet->info_lain = $update['info_lain'];

            if ($request->profile_pict == null) {
                if ($targetVet->save()) {
                    return response([
                        'message' => 'Data Updated Success',
                        'data' => $targetVet
                    ], 200);
                }
            } else if ($request->profile_pict != null) {
                if ($targetVet->profile_pict == null) {
                    $original_name = $request->profile_pict->getClientOriginalName();
                    $generated_name = 'pp' . '-' . time() . '.' . $request->profile_pict->extension();

                    // menyimpan gambar
                    $request->profile_pict->storeAs('public/pp', $generated_name);
                    $targetVet->profile_pict = $generated_name;


                } else if ($targetVet->profile_pict != null) {

                    unlink(public_path('storage/public/pp/' . $targetVet->profile_pict));

                    $original_name = $request->profile_pict->getClientOriginalName();
                    $generated_name = 'pp' . '-' . time() . '.' . $request->profile_pict->extension();
                    // menyimpan gambar
                    $request->profile_pict->storeAs('public/pp', $generated_name);
                    $targetVet->profile_pict = $generated_name;
                }

                if ($targetVet->save()) {
                    return response([
                        'message' => 'Data Updated Success',
                        'data' => $targetVet
                    ], 200);
                }
            }
            return response([
                'message' => 'Failed to update data',
                'data' => null
            ], 400);
        }
        
        if ($role == "Pet Shop") {
            $targetPetShop = PetShop::where('user_id', Auth::user()->id)->first();

            $validator = Validator::make($update, [
                'profile_pict' => 'mimes:jpeg,png,jpg,gif|max:50000',
                'nama' => 'required',
                'alamat' => 'required',
                'kapasitas_penitipan' => 'required',
                'harga_titip' => 'required',
            ], [
                'profile_pict.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif.',
            ]);

            if ($validator->fails()) {
                return response(['message' => $validator->errors()], 400);
            }
            $targetPetShop->nama = $update['nama'];
            $targetPetShop->alamat = $update['alamat'];
            $targetPetShop->kapasitas_penitipan = $update['kapasitas_penitipan'];
            $targetPetShop->harga_titip = $update['harga_titip'];

            if ($request->profile_pict == null) {
                if ($targetPetShop->save()) {
                    return response([
                        'message' => 'Data Updated Success',
                        'data' => $targetPetShop
                    ], 200);
                }
            } else if ($request->profile_pict != null) {
                if ($targetPetShop->profile_pict == null) {
                    $original_name = $request->profile_pict->getClientOriginalName();
                    $generated_name = 'pp' . '-' . time() . '.' . $request->profile_pict->extension();

                    // menyimpan gambar
                    $request->profile_pict->storeAs('public/pp', $generated_name);
                    $targetPetShop->profile_pict = $generated_name;


                } else if ($targetPetShop->profile_pict != null) {

                    unlink(public_path('storage/public/pp/' . $targetPetShop->profile_pict));

                    $original_name = $request->profile_pict->getClientOriginalName();
                    $generated_name = 'pp' . '-' . time() . '.' . $request->profile_pict->extension();
                    // menyimpan gambar
                    $request->profile_pict->storeAs('public/pp', $generated_name);
                    $targetPetShop->profile_pict = $generated_name;
                }

                if ($targetPetShop->save()) {
                    return response([
                        'message' => 'Data Updated Success',
                        'data' => $targetPetShop
                    ], 200);
                }
            }
            return response([
                'message' => 'Failed to update data',
                'data' => null
            ], 400);
        }
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
