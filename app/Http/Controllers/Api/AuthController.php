<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $data = $request->validate([
            'name' => 'required|max:50',
            'email' => 'required|email|unique:pegawais',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ]);

        //enkripsi pada password jika password dan password_confirmation sama
        if ($data['password'] == $data['password_confirmation']) {
            //enkripsi password
            $data['password'] = Hash::make($data['password']);
            //membuat pegawai
          
            $pegawai = Pegawai::create(
                [
                    'id_pegawai' => $this->generateIdPegawai(),
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $data['password']
                ]
            );

            return response()->json([
                'status' => 201,
                'success' => true,
                'data' => $pegawai
            ]);
        } else {
            //jika password dan password_confirmation tidak sama
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => 'Password confirmation does not match'
            ]);
        }
    }

    private function generateIdPegawai()
    {
        $getLast = Pegawai::orderBy('id', 'desc')->first(); //  get last id
        $generateId = $getLast ? (int) substr($getLast->id_pegawai, 3) + 1 : 1; // generate id
        return 'PG-' . str_pad($generateId, 3, '0', STR_PAD_LEFT); // format id
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'pengguna' => 'required',
            'password' => 'required'
        ]);

        // Cari berdasarkan email ATAU name
        $user = Pegawai::where('email', $data['pengguna'])
            ->orWhere('name', $data['pengguna'])
            ->first();

        // Jika user tidak ditemukan
        if (!$user) {
            return response()->json([
                'status' => 401,
                'success' => false,
                'message' => 'Pengguna tidak ditemukan'
            ]);
        }

        // Cek password
        if (!Hash::check($data['password'], $user->password)) {
            return response()->json([
                'status' => 401,
                'success' => false,
                'message' => 'Password salah'
            ]);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        // Login Berhasil
        return response()->json([
            'status' => 200,
            'success' => true,
            'data' => $user,
            'token' => $token,
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Logout success'
        ]);
    }

    public function profile()
    {
        $user = auth()->user();

        return response()->json([
            'id' => $user->id_pegawai,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}
