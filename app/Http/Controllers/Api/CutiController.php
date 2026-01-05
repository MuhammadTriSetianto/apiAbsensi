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


    public function store(Request $request,)
    {
        $request->validate([
            'id_proyek'       => 'required|exists:proyeks,id_proyek',
            'subjek_cuti'     => 'required|string|max:255',
            'tanggal_mulai'   => 'required|before_or_equal:tanggal_selesai',
            'tanggal_selesai' => 'required|after_or_equal:tanggal_mulai',
            'keterangan_cuti' => 'required|string|max:255',
            'surat_cuti'      => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 401);
        }
        $tahun = Carbon::parse($request->tanggal_mulai)->year;

        //  Hitung jumlah hari cuti (inklusif)
        $jumlahHari = Carbon::parse($request->tanggal_mulai)
            ->diffInDays(Carbon::parse($request->tanggal_selesai)) + 1;

        //  Ambil total cuti yang sudah disetujui tahun ini
        $totalCutiTahunIni = Cuti::where('id_karyawan', $user->id_pegawai)
            ->whereYear('tanggal_mulai', $tahun)
            ->where('status_cuti', 'disetujui')
            ->sum(DB::raw("DATEDIFF(tanggal_selesai, tanggal_mulai) + 1"));

        if (($totalCutiTahunIni + $jumlahHari) > 12) {
            return response()->json([
                'message' => 'Pengajuan ditolak. Maksimal cuti 12 hari per tahun.'
            ], 422);
        }

        $formatTanggalMulai = Carbon::parse($request->tanggal_mulai)->format('Y-m-d');
        $formatTanggalSelesai = Carbon::parse($request->tanggal_selesai)->format('Y-m-d');

        // Cek overlap cuti
        $overlap = Cuti::where('id_karyawan', $user->id_pegawai)
            ->where(function ($query) use ($formatTanggalMulai, $formatTanggalSelesai) {
                $query->whereBetween('tanggal_mulai', [$formatTanggalMulai, $formatTanggalSelesai])
                    ->orWhereBetween('tanggal_selesai', [$formatTanggalMulai, $formatTanggalSelesai]);
            })->exists();

        if ($overlap) {
            return response()->json([
                'message' => 'Tanggal cuti bentrok dengan cuti sebelumnya.'
            ], 422);
        }

        //  Cek apakah sudah ada absensi di rentang tanggal cuti
        $absen = Absensi::where('id_pegawai', $user->id_pegawai)
            ->whereBetween('tanggal_absensi', [$request->tanggal_mulai, $request->tanggal_selesai])
            ->exists();

        if ($absen) {
            return response()->json([
                'message' => 'Tidak bisa mengajukan cuti karena sudah ada absensi di tanggal tersebut.'
            ], 422);
        }

        $file =  $request->file('surat_cuti');
        if ($file) {
            $fileName = $request->id_proyek . '_' . $user->id_pegawai . '_' . now()->format('m.d.Y') . '.' . $file->extension();
            $path = $file->storeAs('cuti', $fileName);
        }

        //  Simpan cuti
        $cuti = Cuti::create([
            'id_karyawan'     => $user->id_pegawai,
            'id_cuti'         => $this->generateIdCuti(),
            'id_proyek'       => $request->id_proyek,
            'subjek_cuti'     => $request->subjek_cuti,
            'tanggal_mulai'   => $formatTanggalMulai,
            'tanggal_selesai' => $formatTanggalSelesai,
            'keterangan_cuti' => $request->keterangan_cuti,
            'surat_cuti'      => $path,
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

    public function totalCuti()
    {
        $user = auth('sanctum')->user();

        $totalCuti = Cuti::where('id_karyawan', $user->id_pegawai)
            ->where('status_cuti', 'disetujui')
            ->sum(DB::raw("DATEDIFF(tanggal_selesai, tanggal_mulai) + 1"));

        return response()->json([
            'total_cuti' => $totalCuti,
            'message' => 'Total cuti berhasil diambil',
        ]);
    }


    private function generateIdCuti()
    {
        $getLast = Cuti::orderBy('id', 'desc')->first(); //  get last id
        $generateId = $getLast ? (int) substr($getLast->id_cuti, 3) + 1 : 1; // generate id
        return 'CT-' . str_pad($generateId, 3, '0', STR_PAD_LEFT); // format id
    }
}
