<?php

namespace App\Http\Controllers\Api;

use App\Models\Cuti;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller; 

class CutiController extends Controller
{
    /**
     * Simpan pengajuan cuti
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_karyawan'       => 'required|exists:pegawais,id_pegawai',
            'id_cuti'           => 'required|unique:cutis,id_cuti',
            'id_proyek'         => 'required|exists:proyeks,id_proyek',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan_cuti'   => 'required|string',
        ]);

        $tahun = Carbon::parse($request->tanggal_mulai)->year;

        //  Hitung jumlah hari cuti (inklusif)
        $jumlahHari = Carbon::parse($request->tanggal_mulai)
            ->diffInDays(Carbon::parse($request->tanggal_selesai)) + 1;

        //  Ambil total cuti yang sudah disetujui tahun ini
        $totalCutiTahunIni = Cuti::where('id_karyawan', $request->id_karyawan)
            ->whereYear('tanggal_mulai', $tahun)
            ->where('status_cuti', 'disetujui')
            ->sum(DB::raw("DATEDIFF(tanggal_selesai, tanggal_mulai) + 1"));

        if (($totalCutiTahunIni + $jumlahHari) > 12) {
            return response()->json([
                'message' => 'Pengajuan ditolak. Maksimal cuti 12 hari per tahun.'
            ], 422);
        }

        // Cek overlap cuti
        $overlap = Cuti::where('id_karyawan', $request->id_karyawan)
            ->where(function ($query) use ($request) {
                $query->whereBetween('tanggal_mulai', [$request->tanggal_mulai, $request->tanggal_selesai])
                      ->orWhereBetween('tanggal_selesai', [$request->tanggal_mulai, $request->tanggal_selesai]);
            })->exists();

        if ($overlap) {
            return response()->json([
                'message' => 'Tanggal cuti bentrok dengan cuti sebelumnya.'
            ], 422);
        }

        //  Cek apakah sudah ada absensi di rentang tanggal cuti
        $absen = Absensi::where('id_karyawan', $request->id_karyawan)
            ->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_selesai])
            ->exists();

        if ($absen) {
            return response()->json([
                'message' => 'Tidak bisa mengajukan cuti karena sudah ada absensi di tanggal tersebut.'
            ], 422);
        }

        //  Simpan cuti
        $cuti = Cuti::create([
            'id_karyawan'     => $request->id_karyawan,
            'id_cuti'         => $request->id_cuti,
            'id_proyek'       => $request->id_proyek,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keterangan_cuti' => $request->keterangan_cuti,
            'status_cuti'     => 'proses',
        ]);

        return response()->json([
            'message' => 'Pengajuan cuti berhasil, menunggu persetujuan.',
            'data'    => $cuti
        ], 201);
    }

    /**
     * Setujui cuti (oleh admin)
     */
    public function approve($id)
    {
        $cuti = Cuti::findOrFail($id);

        if ($cuti->status_cuti !== 'proses') {
            return response()->json([
                'message' => 'Cuti sudah diproses.'
            ], 422);
        }

        $cuti->update([
            'status_cuti' => 'disetujui'
        ]);

        return response()->json([
            'message' => 'Cuti berhasil disetujui.'
        ]);
    }

    /**
     * Tolak cuti
     */
    public function reject($id)
    {
        $cuti = Cuti::findOrFail($id);

        $cuti->update([
            'status_cuti' => 'ditolak'
        ]);

        return response()->json([
            'message' => 'Cuti ditolak.'
        ]);
    }
}
