<?php

namespace App\Http\Controllers\Api;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller; 
class NotifikasiController extends Controller
{
    // Ambil semua notifikasi milik user
    public function index()
    {
        $notifikasi = Notifikasi::where('id_user', Auth::user()->id_pegawai)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifikasi);
    }

    // Simpan notifikasi baru
    public function store(Request $request)
    {
        $request->validate([    
            'id_user' => 'required',
            'id_pengirim' => 'required',
            'id_proyek' => 'required',
            'judul' => 'required|string',
            'isi' => 'required|string',
        ]);

        $notifikasi = Notifikasi::create([
            'id_user' => $request->id_user,
            'id_pengirim' => $request->id_pengirim,
            'id_proyek' => $request->id_proyek,
            'judul' => $request->judul,
            'isi' => $request->isi,
            'status' => 'belum_dibaca',
        ]);

        return response()->json([
            'message' => 'Notifikasi berhasil dibuat',
            'data' => $notifikasi
        ], 201);
    }

    // Detail notifikasi
    public function show($id)
    {
        $notifikasi = Notifikasi::findOrFail($id);

        return response()->json($notifikasi);
    }

    // Tandai notifikasi sebagai dibaca
    public function markAsRead($id)
    {
        $notifikasi = Notifikasi::findOrFail($id);
        $notifikasi->update([
            'status' => 'dibaca'
        ]);

        return response()->json([
            'message' => 'Notifikasi telah dibaca'
        ]);
    }

    // Hapus notifikasi
    public function destroy($id)
    {
        $notifikasi = Notifikasi::findOrFail($id);
        $notifikasi->delete();

        return response()->json([
            'message' => 'Notifikasi berhasil dihapus'
        ]);
    }
}
