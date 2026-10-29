<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    //

    public function index()
    {
        $user = User::with('lokasi')
            ->orderBy("name")
            ->get()
            ->map(function ($nmuser, $index) {
                return [
                    'no' => $index + 1,
                    'id' => $nmuser->id,
                    'name' => $nmuser->name,
                    'username' => $nmuser->username,
                    'jenis_kelamin' => $nmuser->jenis_kelamin,
                    'no_hp' => $nmuser->no_hp,
                    'level' => $nmuser->level,
                    'penugasan' => $nmuser->penugasan,
                    'id_lokasi' => $nmuser->id_lokasi,

                    // data lokasi
                    'lokasi' => [
                        'nama_tempat' => $nmuser->lokasi->nama_tempat ?? null,
                        'lat' => $nmuser->lokasi->lat ?? null,
                        'lang' => $nmuser->lokasi->lang ?? null,
                        'absen_datang' => $nmuser->lokasi->absen_datang ?? null,
                        'absen_pulang' => $nmuser->lokasi->absen_pulang ?? null,
                        'radius' => $nmuser->lokasi->radius ?? null,

                    ]
                ];
            });

        return response()->json([
            "data" => $user,
        ]);
    }
    
    //register
    public function regis(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'name' => "required|regex:/^[a-zA-Z0-9_ .-]+$/",
            'username' => "required|unique:users|regex:/^[a-zA-Z0-9_ .-]+$/",
            'jenis_kelamin' => "required", 
            'no_hp' => "required", 
            'level' => "required",
            'id_lokasi' => "required",
            'password' => "required|min:3|max:20",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "invalid field",
                "errors" => $validator->errors(),
            ], 422);
        }
        
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'level' => $request->level,
            'jenis_kelamin' => $request->jenis_kelamin,
            'no_hp' => $request->no_hp,
            'id_lokasi' => $request->id_lokasi,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            "message" => "success register"
        ]);
    }

    //login
    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => "required|regex:/^[a-zA-Z0-9_ .-]+$/",
            'password' => "required|min:3|max:20",
        ]);
        if($validator->fails()) {
            return response()->json([
                "message" => "invalid field",
                "errors" => $validator->errors(),
            ], 422);
        }

        if(!Auth::attempt($request->only("username", "password"))) return response()->json([
            "message" => "username or password wrong",
        ], 401);

        $user = User::where("username", $request->username)->first();
        $token = $user->createToken("auth_token")->plainTextToken;
        
        return response()->json([
            "message" => "success login",
            
            "id" => $user->id,
            "level" => $user->level,
            "token" => $token,
            "nama" => $user->name,
        ]);
    }

    public function loginUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => "required|regex:/^[a-zA-Z0-9_ .-]+$/",
            'password' => "required|min:3|max:20",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "invalid field",
                "errors" => $validator->errors(),
            ], 422);
        }

        if (!Auth::attempt($request->only("username", "password"))) {
            return response()->json([
                "message" => "username or password wrong",
            ], 401);
        }

        $user = User::with('lokasi')
            ->where("username", $request->username)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        // buat token setelah login sukses
        $token = $user->createToken("auth_token")->plainTextToken;

        return response()->json([
            "message" => "success login",
            "token" => $token,

            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "username" => $user->username,
                "jenis_kelamin" => $user->jenis_kelamin,
                "no_hp" => $user->no_hp,
                "level" => $user->level,
                "penugasan" => $user->penugasan,
                "id_lokasi" => $user->id_lokasi,

                "lokasi" => [
                    "nama_tempat" => $user->lokasi->nama_tempat ?? null,
                    "lat" => $user->lokasi->lat ?? null,
                    "lang" => $user->lokasi->lang ?? null,
                    "absen_datang" => $user->lokasi->absen_datang ?? null,
                    "absen_pulang" => $user->lokasi->absen_pulang ?? null,
                    'radius' => $user->lokasi->radius ?? null,
                ]
            ]
        ]);
    }
    //check users
    public function ceking(){
        $user = Auth::user();
        return response()->json([
            "level" => $user->level,
        ]);
    }

    //logout
    public function logout(Request $request) {
        $user = Auth::user();
        $user->currentAccessToken()->delete();

        return response()->json([
            "message" => "success logout",
        ]);
    }
    
    public function show($id)
    {
        $user = User::find($id);

        if(!$user){
            return response()->json([
                "message" => "User tidak ditemukan"
            ],404);
        }

        return response()->json([
            "data" => $user
        ]);
    }

    public function update_penugasan(Request $request, $id) {
        $user = User::find($id);

        if(!$user){
            return response()->json([
                "message" => "User tidak ditemukan"
            ],404);
        }

        $validator = Validator::make($request->all(), [
            'penugasan' => "required|boolean",
        ]);

        if ($validator->fails()) {

            return response()->json([
                "message" => "invalid field",
                "errors" => $validator->errors(),
            ], 422);
        }

        $user->penugasan = $request->penugasan;
        $user->save();

        return response()->json([
            "message" => "success update"
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if(!$user){
            return response()->json([
                "message" => "User tidak ditemukan"
            ],404);
        }

        $validator = Validator::make($request->all(), [

            'name' => "required|regex:/^[a-zA-Z0-9_ .-]+$/",

            'username' => "required|regex:/^[a-zA-Z0-9_ .-]+$/",

            'jenis_kelamin' => "required",

            'no_hp' => "required",

            'level' => "required",

            'id_lokasi' => "required",

        ]);

        if ($validator->fails()) {

            return response()->json([
                "message" => "invalid field",
                "errors" => $validator->errors(),
            ], 422);
        }

        //CHECK USERNAME
 
        $cekUsername = User::where('username', $request->username)
            ->where('id', '!=', $id)
            ->first();

        if($cekUsername){

            return response()->json([
                "message" => "Username sudah digunakan"
            ],422);
        }

        $user->name = $request->name;

        $user->username = $request->username;

        $user->jenis_kelamin = $request->jenis_kelamin;

        $user->no_hp = $request->no_hp;

        $user->level = $request->level;

        $user->id_lokasi = $request->id_lokasi;

        /*
        |--------------------------------------------------------------------------
        | PASSWORD OPTIONAL
        |--------------------------------------------------------------------------
        */

        if($request->password){

            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            "message" => "success update"
        ]);
    }

    //delete
    public function destroy(User $user, $id)
    {
        $user = User::find($id);
        if(!$user) return response()->json([
            "message" => "User not found",
        ], 404);

        $user->delete();

        return response()->json([
            "message" => "success delete",
        ]);
    }

    public function search_nama($nama)
    {
        $idLogin = Auth::id();

        $users = User::with('lokasi')
            ->where('name', 'like', '%' . $nama . '%')
            ->where('level', '!=', 'Admin')
            ->where('id', '!=', $idLogin)
            ->get();

        if ($users->isEmpty()) {
            return response()->json([
                "message" => "Data tidak ditemukan",
                "data" => []
            ], 404);
        }

        $data = $users->map(function ($user) {
            return [
                "id" => $user->id,
                "name" => $user->name,
                "username" => $user->username,
                "jenis_kelamin" => $user->jenis_kelamin,
                "no_hp" => $user->no_hp,
                "level" => $user->level,
                "id_lokasi" => $user->id_lokasi,

                "lokasi" => [
                    "nama_tempat" => optional($user->lokasi)->nama_tempat,
                    "lat" => optional($user->lokasi)->lat,
                    "lang" => optional($user->lokasi)->lang,
                    "absen_datang" => optional($user->lokasi)->absen_datang,
                    "absen_pulang" => optional($user->lokasi)->absen_pulang,
                    "radius" => optional($user->lokasi)->radius,
                ]
            ];
        });

        return response()->json([
            "message" => "success",
            "total" => $data->count(),
            "data" => $data
        ]);
    }
    
}
