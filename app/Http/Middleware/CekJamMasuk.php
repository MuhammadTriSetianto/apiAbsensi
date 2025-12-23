<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Absensi;


class CekJamMasuk
{
    public function handle(Request $request, Closure $next)
    {
        $sekarang = Carbon::now();
        $jamBuka = Carbon::createFromTime(7, 0, 0);   // 07:00
        $jamTutup = Carbon::createFromTime(8, 0, 0);  // 08:00

        // Sebelum jam buka
        if ($sekarang->lt($jamBuka)) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi belum dibuka, mulai pukul 07:00'
            ], 403);
        }

        // Lewat jam tutup → ALPHA
        if ($sekarang->gt($jamTutup)) {

            // OPTIONAL: simpan alpha otomatis
            if ($request->id_pegawai && $request->id_proyek) {
                Absensi::firstOrCreate(
                    [
                        'id_pegawai' => $request->id_pegawai,
                        'tanggal_absensi' => Carbon::today(),
                    ],
                    [
                        'id_proyek' => $request->id_proyek,
                        'jam_masuk' => null,
                        'jam_pulang' => null,
                        'keterangan_absensi' => 'alpha'
                    ]
                );
            }

            return response()->json([
                'success' => false,
                'message' => 'Anda terlambat, status ALPHA'
            ], 403);
        }

        return $next($request);
    }
}
