<?php

namespace App\Http\Controllers\Api;

use App\Models\Izin;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
class IzinController extends Controller
{
    public function index()
    {

        $data = Izin::with('pegawai', 'proyek')->get();

        return response()->json([
            'success' => true,
            'data' => $data,    
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_pegawai'        => 'required|exists:pegawais,id_pegawai',
            'id_proyek'         => 'required|exists:proyeks,id_proyek',
            'keterangan_izin'   => 'required|string',
            'jenis_izin'        => 'required|in:sakit,lainnya',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        // Hitung hari izin
        $jumlahHari = Carbon::parse($data['tanggal_mulai'])
            ->diffInDays(Carbon::parse($data['tanggal_selesai'])) + 1;

        // Cek overlap izin
        $overlap = Izin::where('id_pegawai', $data['id_pegawai'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('tanggal_mulai', [$data['tanggal_mulai'], $data['tanggal_selesai']])
                  ->orWhereBetween('tanggal_selesai', [$data['tanggal_mulai'], $data['tanggal_selesai']]);
            })->exists();

        if ($overlap) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal izin bentrok dengan izin sebelumnya.'
            ], 422);
        }

        // Cek absensi
        $absen = Absensi::where('id_pegawai', $data['id_pegawai'])
            ->whereBetween('tanggal', [$data['tanggal_mulai'], $data['tanggal_selesai']])
            ->exists();

        if ($absen) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa izin karena sudah ada absensi.'
            ], 422);
        }

        $izin = Izin::create([
            'id_pegawai'        => $data['id_pegawai'],
            'id_proyek'         => $data['id_proyek'],
            'keterangan_izin'   => $data['keterangan_izin'],
            'jenis_izin'        => $data['jenis_izin'],
            'tanggal_mulai'     => $data['tanggal_mulai'],
            'tanggal_selesai'   => $data['tanggal_selesai'],
            'status_izin'       => 'proses'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Izin berhasil diajukan.',
            'data' => $izin
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => Izin::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $izin = Izin::findOrFail($id);

        $data = $request->validate([
            'keterangan_izin' => 'sometimes|required|string',
            'jenis_izin'      => 'sometimes|required|in:sakit,lainnya',
            'tanggal_mulai'   => 'sometimes|required|date',
            'tanggal_selesai' => 'sometimes|required|date|after_or_equal:tanggal_mulai',
            'status_izin'     => 'sometimes|required|in:proses,disetujui,ditolak'
        ]);

        // Jika update tanggal, cek absensi ulang
        if (isset($data['tanggal_mulai'], $data['tanggal_selesai'])) {
            $absen = Absensi::where('id_pegawai', $izin->id_pegawai)
                ->whereBetween('tanggal', [$data['tanggal_mulai'], $data['tanggal_selesai']])
                ->exists();

            if ($absen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal izin bertabrakan dengan absensi.'
                ], 422);
            }
        }

        $izin->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data izin berhasil diperbarui.',
            'data' => $izin
        ]);
    }

    public function destroy($id)
    {
        Izin::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data izin berhasil dihapus.'
        ]);
    }
}
