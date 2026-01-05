<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ===============================
    // REGISTER
    // ===============================
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:50',
            'email' => 'required|email|unique:pegawais',
            'password' => 'required|confirmed',
        ]);

        $pegawai = Pegawai::create([
            'id_pegawai' => $this->generateIdPegawai(),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'status' => 201,
            'success' => true,
            'data' => $pegawai
        ]);
    }

    private function generateIdPegawai()
    {
        $last = Pegawai::orderBy('id', 'desc')->first();
        $number = $last ? (int) substr($last->id_pegawai, 3) + 1 : 1;
        return 'PG-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
    public function login(Request $request)
    {
        $data = $request->validate([
            'pengguna' => 'required',
            'password' => 'required'
        ]);

        $user = Pegawai::where('email', $data['pengguna'])
            ->orWhere('name', $data['pengguna'])
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'status' => 401,
                'success' => false,
                'message' => 'Email/Username atau password salah'
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 200,
            'success' => true,
            'data' => [
                'id_pegawai' => $user->id_pegawai,
                'name' => $user->name,
                'email' => $user->email,
                'image' => $user->image
                    ? url(Storage::url($user->image))
                    : null,
            ],
            'token' => $token,
        ]);
    }

    // ===============================
    // LOGOUT
    // ===============================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Logout success'
        ]);
    }

    // ===============================
    // PROFILE
    // ===============================
    public function profile()
    {
        $user = auth('sanctum')->user();

        return response()->json([
            'status' => 200,
            'success' => true,
            'data' => [
                'id_pegawai' => $user->id_pegawai,
                'name' => $user->name,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'alamat' => $user->alamat,
                'image' => $user->image
                    ? url(Storage::url($user->image))
                    : null,
            ]
        ]);
    }

    // ===============================
    // UPDATE PROFILE
    // ===============================
    public function updateProfile(Request $request)
    {
        $authUser = $request->user();
        $pegawai = Pegawai::where('id_pegawai', $authUser->id_pegawai)->firstOrFail();

        $data = $request->validate([
            'name' => 'nullable|max:50',
            'no_hp' => 'nullable|max:15',
            'alamat' => 'nullable|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('image')) {

            if ($pegawai->image && Storage::disk('public')->exists($pegawai->image)) {
                Storage::disk('public')->delete($pegawai->image);
            }

            $path = $request->image->store('profile', 'public');
            $pegawai->image = $path;
        }

        $pegawai->update([
            'name' => $data['name'] ?? $pegawai->name,
            'no_hp' => $data['no_hp'] ?? $pegawai->no_hp,
            'alamat' => $data['alamat'] ?? $pegawai->alamat,
        ]);

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Profile berhasil diperbarui',
            'data' => [
                'id_pegawai' => $pegawai->id_pegawai,
                'name' => $pegawai->name,
                'no_hp' => $pegawai->no_hp,
                'alamat' => $pegawai->alamat,
                'image' => $pegawai->image
                    ? url(Storage::url($pegawai->image))
                    : null,
            ]
        ]);
    }
}
