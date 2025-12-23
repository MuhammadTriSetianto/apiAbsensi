<?php

namespace App\Http\Controllers\Api;
use App\Models\UserProyeks;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
class UserProyekController extends Controller
{
    public function index()
    {

        $data = UserProyeks::with('user', 'proyek')->get();
        
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
            'status' => 201

        ]);
    }
    
public function show($idPegawai, $idProyek)
{
    return UserProyeks::where('id_pegawai', $idPegawai)
        ->where('id_proyek', $idProyek)
        ->firstOrFail();
}

public function destroy($idPegawai, $idProyek)
{
    UserProyeks::where('id_pegawai', $idPegawai)
        ->where('id_proyek', $idProyek)
        ->delete();

    return response()->json(['message' => 'Deleted']);
}

}
