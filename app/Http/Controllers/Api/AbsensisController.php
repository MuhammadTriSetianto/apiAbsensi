<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use App\Models\Absensi;
use App\Models\FotoAbsensi;
use App\Models\Izin;
use App\Models\Cuti;
use App\Models\Proyek;
use App\Models\UserProyeks;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AbsensisController extends Controller
{
    public function masuk(Request $request)
    {
        $request->validate([
            'id_pegawai' => 'required|exists:pegawais,id_pegawai',
            'id_proyek'  => 'required|exists:proyeks,id_proyek',
            'latitude'   => 'required|numeric',
            'longitude'  => 'required|numeric',
            'foto'       => 'required|image'
        ]);

        $hariIni = Carbon::today();

        // 1️⃣ Cek pegawai terdaftar di proyek
        if (!UserProyeks::where('id_pegawai', $request->id_pegawai)
            ->where('id_proyek', $request->id_proyek)
            ->exists()) {
            return response()->json([
                'error' => 'Pegawai bukan bagian dari proyek ini'
            ], 403);
        }

        // 2️⃣ Cek CUTI (PALING PENTING)
        $sedangCuti = Cuti::where('id_karyawan', $request->id_pegawai)
            ->where('status_cuti', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $hariIni)
            ->whereDate('tanggal_selesai', '>=', $hariIni)
            ->exists();

        if ($sedangCuti) {
            return response()->json([
                'error' => 'Pegawai sedang cuti dan tidak dapat melakukan absensi'
            ], 403);
        }

        // 3️⃣ Cek IZIN
        $sedangIzin = Izin::where('id_pegawai', $request->id_pegawai)
            ->where('status_izin', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $hariIni)
            ->whereDate('tanggal_selesai', '>=', $hariIni)
            ->exists();

        if ($sedangIzin) {
            return response()->json([
                'error' => 'Pegawai sedang izin dan tidak dapat melakukan absensi'
            ], 403);
        }

        // 4️⃣ Cek absensi hari ini
        if (Absensi::where('id_pegawai', $request->id_pegawai)
            ->whereDate('tanggal_absensi', $hariIni)
            ->exists()) {
            return response()->json([
                'error' => 'Pegawai sudah melakukan absensi hari ini'
            ], 400);
        }

        // 5️⃣ Validasi GPS
        $proyek = Proyek::where('id_proyek', $request->id_proyek)->firstOrFail();

        $jarak = $this->hitungJarak(
            $request->latitude,
            $request->longitude,
            $proyek->lat_proyek,
            $proyek->long_proyek
        );

        if ($jarak > 50) {
            return response()->json([
                'error' => 'Anda berada di luar radius lokasi proyek'
            ], 400);
        }

        // 6️⃣ Simpan foto
        $path = $request->file('foto')->store('absensi_foto', 'public');

        // 7️⃣ Simpan absensi
        $absen = Absensi::create([
            'id_pegawai' => $request->id_pegawai,
            'id_proyek' => $request->id_proyek,
            'tanggal_absensi' => $hariIni,
            'jam_masuk' => now()->format('H:i:s'),
            'jam_pulang' => null,
            'keterangan_absensi' => 'hadir',
        ]);

        // 8️⃣ Simpan foto absensi
        FotoAbsensi::create([
            'id_absensi' => $absen->id_absensi,
            'foto_absensi' => $path,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'message' => 'Absen masuk berhasil',
            'data' => $absen
        ], 201);
    }

    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        return $R * (2 * atan2(sqrt($a), sqrt(1-$a)));
    }
}
