<?php

namespace App\Http\Controllers\Api;

use App\Models\UserProyeks;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserProyekController extends Controller
{
    public function index()
    {

        $data = UserProyeks::with('pegawai', 'proyek')->get();

        return response()->json([
            'massage' => 'data proyek telah di dapat',
            'data' => $data,
            'status' => 200
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pegawai' => 'required|exists:pegawais,id_pegawai',
            'id_proyek' => 'required|exists:proyeks,id_proyek',
            'jabatan' => 'required|string|max:20',
        ]);

        $perkerja = UserProyeks::where('id_proyek', $request->id_proyek)->exists();

        if ($perkerja) {
            return response()->json([ 
                'message' => 'Pegawai sudah terdaftar di salah satu proyek ini',
                'status' => 403
            ]);
        }
        $data = UserProyeks::create([
            'id_pegawai' => $request->id_pegawai,
            'id_proyek' => $request->id_proyek,
            'jabatan' => $request->jabatan,
        ]);

        return response()->json([
            'data' => $data,
            'status' => 201

        ]);
    }


    public function upadate(Request $request)
    {
        $request->validate([
            'id_pegawai' => 'required|exists:pegawais,id_pegawai',
            'id_proyek' => 'required|exists:proyeks,id_proyek',
            'jabatan' => 'required|string|max:20',
        ]);

        $data = UserProyeks::create([
            'id_pegawai' => $request->id_pegawai,
            'id_proyek' => $request->id_proyek,
            'jabatan' => $request->jabatan,
        ]);

        return response()->json([
            'data' => $data,
            'status' => 201,
            'message' => 'Data berhasil di update'
        ]);
    }

   public function show()
{
    $user = auth('sanctum')->user();

    $data = UserProyeks::with(['pegawai', 'proyek'])
        ->where('id_pegawai', $user->id_pegawai)
        ->get();

    return response()->json([
        'status' => 200,
        'data' => $data,
    ]);
}



    public function destroyAll($idProyek)
    {
        UserProyeks::where('id_proyek', $idProyek)
            ->delete();

        return response()->json(['message' => 'Deleted']);
    }
    public function destroy($idPegawai)
    {
        UserProyeks::where('id_proyek', $idPegawai)
            ->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
