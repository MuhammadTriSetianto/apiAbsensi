<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proyek;
use Illuminate\Http\Request;

class ProyekController extends Controller
{
    public function index()
    {
        $proyek = Proyek::all();
        return response()->json([
            'status' => 200,
            'success' => true,
            'data' => $proyek
        ]);
    }

    public function show($id)
    {
        $proyek = Proyek::findOrFail($id);
        return response()->json([
            'status' => 200,
            'success' => true,
            'data' => $proyek
        ]);
    }

    public function create_project(Request $request)
    {
        $data_proyek = $request->validate([
            'nama_proyek'   => 'required|max:50',
            'lokasi_proyek' => 'required|max:255',
            'deskripsi'     => 'required|max:225',
            'log_proyek'    => 'required|numeric',
            'lat_proyek'    => 'required|numeric',
        ]);
    
        
        $proyek = Proyek::create([
            'nama_proyek' => $data_proyek['nama_proyek'],
            'deskripsi' => $data_proyek['deskripsi'],
            'lokasi_proyek' => $data_proyek['lokasi_proyek'],
            'long_proyek' => $data_proyek['log_proyek'],
            'lat_proyek' => $data_proyek['lat_proyek'],
        ]);
        return response()->json([
            'status' => 201,
            'success' => true,
            'data' => $proyek
        ]);
    }

    public function update_project(Request $request, $id)
    {
        $proyek = Proyek::find($id);

        if (!$proyek) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'Data proyek tidak ditemukan'
            ], 404);
        }

        $data_proyek = $request->validate([
            'nama_proyek'   => 'required|max:50',
            'lokasi_proyek' => 'required|max:255',
            'deskripsi'     => 'nullable',
            'log_proyek'    => 'nullable',
            'lat_proyek'    => 'nullable',
        ]);

        $proyek->update([
            'nama_proyek'   => $data_proyek['nama_proyek'],
            'lokasi_proyek' => $data_proyek['lokasi_proyek'],
            'long_proyek'   => $data_proyek['log_proyek'],
            'lat_proyek'    => $data_proyek['lat_proyek'],
        ]);

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Data proyek berhasil diperbarui',
            'data' => $proyek
        ]);
    }


    public function delete_project($id)
    {
        $proyek = Proyek::find($id);
        $proyek->delete();
        return response()->json([
            'status' => 200,
            'success' => true,
            'data' => $proyek
        ]);
    }
}
