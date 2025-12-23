<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Service\CekIzinAtauCutiController as ServiceCekIzinAtauCutiController;
use App\Models\Absensi;
use App\Models\FotoAbsensi;
use App\Models\Proyek;
use App\Models\UserProyeks;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AbsensisController extends Controller
{
    public function masuk(Request $request, $id_proyek, $id_pegawai, ServiceCekIzinAtauCutiController $izinCutiService)
    {
        // 1️Validasi request
        $request->validate([
            'id_pegawai' => 'required|exists:pegawais,id_pegawai',
            'latitude'   => 'required|numeric',
            'longitude'  => 'required|numeric',
            'foto'       => 'required|image|mimes:jpg,jpeg,png'
        ]);

        $hariIni = Carbon::today();

        // 2Cek pegawai terdaftar di proyek
        if (!UserProyeks::where('id_pegawai', $id_pegawai)
            ->where('id_proyek', $id_proyek)
            ->exists()) {
            return response()->json([
                'error' => 'Pegawai bukan bagian dari proyek ini'
            ], 403);
        }


        // cek status izin atau cuti from pegawai
        $cek  = $izinCutiService->cekIzinAtauCuti($id_pegawai, $hariIni);

        if ($cek['status']) {
            return response()->json([
                'error' => $cek['message']
            ]);
        }

        // 5Cek absensi hari ini
        if (Absensi::where('id_pegawai', $id_pegawai)
            ->whereDate('tanggal_absensi', $hariIni)
            ->exists()
        ) {
            return response()->json([
                'error' => 'Pegawai sudah melakukan absensi hari ini'
            ], 400);
        }

        // Validasi GPS
        $proyek = Proyek::where('id_proyek', $id_proyek)->firstOrFail();

        $jarak = $this->hitungJarak(
            $request->latitude,
            $request->longitude,
            $proyek->lat_proyek,
            $proyek->long_proyek
        );

        if ($jarak >= 10) {
            return response()->json([
                'error' => 'Anda berada di luar radius lokasi proyek'
            ], 400);
        }

        // 7Decode & simpan foto BASE64
        $image = $request->file('foto');
        $namafile = $id_pegawai . '_' . now()->format('d-m-Y_H-i') . '.' . $image->extension();

        $path = $request->file('foto')->storeAs('absensis', $namafile, 'public');
        // Simpan absensi
        $absen = Absensi::create([
            'id_pegawai' => $request->id_pegawai,
            'id_proyek' => $id_proyek,
            'tanggal_absensi' => $hariIni,
            'jam_masuk' => now()->format('H:i:s'),
            'jam_pulang' => null,
            'keterangan_absensi' => "null",
        ]);
        $id_absen = Absensi::where('id_pegawai', $id_pegawai)
            ->whereDate('tanggal_absensi', $hariIni)
            ->first();
        //  Simpan foto absensi
        FotoAbsensi::create([
            'id_absensi' => $id_absen->id_absensi,
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
        $id_pegawai,
        ServiceCekIzinAtauCutiController $izinCutiService
    ) {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto'      => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

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

    public function getMasukHariIni($id_pegawai)
    {
        $hariIni = Carbon::today();

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

    public function getAllMasukByUser($id_pegawai)
    {
        $data = Absensi::with('foto')
            ->where('id_pegawai', $id_pegawai)
            ->whereNotNull('jam_masuk')
            ->orderBy('tanggal_absensi', 'desc')
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data absensi masuk tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data->map(function ($absen) {
                return [
                    'id_absensi' => $absen->id_absensi,
                    'id_pegawai' => $absen->id_pegawai,
                    'id_proyek' => $absen->id_proyek,
                    'tanggal_absensi' => $absen->tanggal_absensi,
                    'jam_masuk' => $absen->jam_masuk,
                    'keterangan_absensi' => $absen->keterangan_absensi,
                    'foto' => $absen->foto
                        ? 'data:image/png;base64,' . $absen->foto->foto_absensi
                        : null,
                    'latitude' => $absen->foto->latitude ?? null,
                    'longitude' => $absen->foto->longitude ?? null,
                ];
            })
        ], 200);
    }
}
