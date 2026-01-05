<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Service\CekIzinAtauCutiController as ServiceCekIzinAtauCutiController;
use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\FotoAbsensi;
use App\Models\Izin;
use App\Models\Proyek;
use App\Models\UserProyeks;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class AbsensisController extends Controller
{
    public function masuk(Request $request, ServiceCekIzinAtauCutiController $izinCutiService)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto'      => 'required|image|mimes:jpg,jpeg,png|max:2048',

        ]);

        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 401);
        }
        $id_proyek = UserProyeks::where('id_pegawai', $user->id_pegawai)->value('id_proyek');
        $id_pegawai = $user->id_pegawai;

        $hariIni = Carbon::today();

        // Pegawai terdaftar di proyek
        if (!UserProyeks::where('id_pegawai', $id_pegawai)->where('id_proyek', $id_proyek)->exists()) {
            return response()->json(['message' => 'Pegawai bukan bagian dari proyek ini'], 403);
        }

        // Cek izin / cuti
        $cek = $izinCutiService->cekIzinAtauCuti($id_pegawai, $hariIni);
        if ($cek['status']) {
            return response()->json(['message' => $cek['message']], 403);
        }

        // Cek absensi hari ini
        if (Absensi::where('id_pegawai', $id_pegawai)->whereDate('tanggal_absensi', $hariIni)->exists()) {
            return response()->json(['message' => 'Pegawai sudah absen hari ini'], 400);
        }

        // Validasi lokasi
        $proyek = Proyek::where('id_proyek', $id_proyek)->firstOrFail();
        $jarak = $this->hitungJarak(
            $request->latitude,
            $request->longitude,
            $proyek->lat_proyek,
            $proyek->long_proyek
        );

        if ($jarak > 10) {
            return response()->json(['message' => 'Di luar radius proyek'], 400);
        }

        // Upload foto
        $fileName = $id_pegawai . '_' . now()->format('Ymd_His') . '.' . $request->foto->extension();
        $path = $request->foto->storeAs('absensi/masuk', $fileName, 'public');

        // Simpan absensi
        $absen = Absensi::create([
            'id_pegawai' => $id_pegawai,
            'id_proyek' => $id_proyek,
            'tanggal_absensi' => $hariIni,
            'jam_masuk' => now()->format('H:i:s'),
            'keterangan_absensi' => 'null',
        ]);

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


    public function pulang(
        Request $request,
        ServiceCekIzinAtauCutiController $izinCutiService
    ) {
        $user = auth('sanctum')->user();

        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto'      => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if (!$user) {
            return response()->json(['message' => 'User harus login'], 401);
        }
        $id_pegawai = $user->id_pegawai;

        $hariIni = Carbon::today();

        //  Cek izin / cuti
        $cek = $izinCutiService->cekIzinAtauCuti($id_pegawai, $hariIni);
        if ($cek['status']) {
            return response()->json([
                'success' => false,
                'message' => $cek['message']
            ], 403);
        }

        //  Ambil absensi hari ini
        $absen = Absensi::where('id_pegawai', $id_pegawai)
            ->whereDate('tanggal_absensi', $hariIni)
            ->first();

        if (!$absen) {
            return response()->json([
                'success' => false,
                'message' => 'Belum melakukan absensi masuk'
            ], 404);
        }

        if ($absen->jam_pulang) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi pulang'
            ], 400);
        }

        //  Validasi GPS
        $proyek = Proyek::where('id_proyek', $absen->id_proyek)->firstOrFail();
        $jarak = $this->hitungJarak(
            $request->latitude,
            $request->longitude,
            $proyek->lat_proyek,
            $proyek->long_proyek
        );

        if ($jarak > 10) {
            return response()->json([
                'success' => false,
                'message' => 'Anda berada di luar radius lokasi proyek'
            ], 400);
        }

        //  Simpan foto pulang
        $namafile = $id_pegawai . '_' . now()->format('Ymd_His') . '.' . $request->foto->extension();
        $path = $request->foto->storeAs('absensi/pulang', $namafile, 'public');

        //  Update absensi
        $absen->update([
            'jam_pulang' => now()->format('H:i:s'),
            'keterangan_absensi' => 'hadir'
        ]);

        // 🖼 Update / create foto absensi
        FotoAbsensi::updateOrCreate(
            ['id_absensi' => $absen->id_absensi],
            [
                'foto_pulang' => $path,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil',
            'data' => [
                'id_absensi' => $absen->id_absensi,
                'jam_pulang' => $absen->jam_pulang
            ]
        ]);
    }



    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000; // meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        return $R * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    public function getMasukHariIni()
    {
        $user = auth('sanctum')->user();
        $hariIni = Carbon::today();
        $id_pegawai = $user->id_pegawai;
        $absensi = Absensi::with('foto')
            ->where('id_pegawai', $id_pegawai)
            ->whereDate('tanggal_absensi', $hariIni)
            ->first();

        if (!$absensi) {
            return response()->json([
                'success' => false,
                'message' => 'Belum melakukan absensi hari ini'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_absensi' => $absensi->id_absensi,
                'id_pegawai' => $absensi->id_pegawai,
                'id_proyek' => $absensi->id_proyek,
                'tanggal_absensi' => $absensi->tanggal_absensi,
                'jam_masuk' => $absensi->jam_masuk,
                'keterangan_absensi' => $absensi->keterangan_absensi,
                'foto' => $absensi->foto
                    ? 'data:image/png;base64,' . $absensi->foto->foto_absensi
                    : null,
                'latitude' => $absensi->foto->latitude ?? null,
                'longitude' => $absensi->foto->longitude ?? null,
            ]
        ], 200);
    }



    public function getAllMasukByUser()
    {
        $user = auth('sanctum')->user();
        $thisMonth = Carbon::now();

        $absensi = Absensi::with(['fotoAbsensi', 'pegawai', 'proyek'])
            ->where('id_pegawai', $user->id_pegawai)
            ->where('keterangan_absensi', 'hadir')
            ->whereBetween(
                'tanggal_absensi',
                [
                    $thisMonth->copy()->startOfMonth(),
                    $thisMonth->copy()->endOfMonth(),
                ]
            )
            ->orderBy('tanggal_absensi', 'desc')
            ->get();

        $izin = Izin::with(['user', 'proyek'])
            ->where('id_pegawai', $user->id_pegawai)
            ->where('status_izin', 'disetujui')
            ->whereMonth('tanggal_mulai', $thisMonth->month)
            ->whereYear('tanggal_mulai', $thisMonth->year)
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        $totalCuti = Cuti::where('id_karyawan', $user->id_pegawai)
            ->where('status_cuti', 'disetujui')
            ->sum(DB::raw("DATEDIFF(tanggal_selesai, tanggal_mulai) + 1"));


        return response()->json([
            'success' => true,
            'absensi' => $absensi,
            'izin' => $izin,
            'total_cuti' => $totalCuti,
            'message' => 'Data absensi berhasil diambil'
        ]);
    }
}
