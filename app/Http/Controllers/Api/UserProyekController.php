<?php

namespace App\Http\Controllers;

use App\Models\UserProyeks;
use Illuminate\Http\Request;

class UserProyekController extends Controller
{
    public function index()
    {
        return UserProyeks::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pegawai' => 'required',
            'id_proyek' => 'required',
            'jabatan' => 'required|string|max:20',
        ]);

        $data = UserProyeks::create([
            'id_pegawai' => $request->id_pegawai,
            'id_proyek' => $request->id_proyek,
            'jabatan' => $request->jabatan,
        ]);

        return response()->json($data, 201);
    }

    public function show($idPegawai, $idProyek)
    {
        //mengembalikan data userproyek berdasarkan id_pegawai dan id_proyek
        return UserProyeks::where('id_pegawai', $idPegawai)
                         ->where('id_proyek', $idProyek)
                         ->firstOrFail();
    }

    public function destroy($idPegawai, $idProyek)
    {
        UserProyeks::where('id_pegawai', $idPegawai)
                  ->where('id_proyek', $idProyek)
                  ->delete();

        return response()->json(['message' => "Deleted"]);
    }
}
