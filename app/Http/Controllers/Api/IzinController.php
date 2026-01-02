<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Izin;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class IzinController extends Controller
{
    public function index()
    {
        $data = Izin::with('pegawai', 'proyek')->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    public function requestbuatizin(Request $request, $id_pegawai, $id_proyek)
    {
        // VALIDASI INPUT
        $data = $request->validate([
            'keterangan_izin' => 'required|string|max:255',
            'subjek_izin' => 'required|string|max:255',
            'suratizin' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'jenis_izin' => '|string|sometimes|in:sakit,lainnya',
            'tanggal_mulai'   => 'required|before_or_equal:tanggal_selesai',
            'tanggal_selesai' => 'required|after_or_equal:tanggal_mulai',

        ]);
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 401);
        }
        // FORMAT TANGGAL
        $formatTanggalMulai = Carbon::parse($data['tanggal_mulai'])->format('Y-m-d');
        $formatTanggalSelesai = Carbon::parse($data['tanggal_selesai'])->format('Y-m-d');

        //HITUNG JUMLAH HARI IZIN
        $jumlahHari = Carbon::parse($data['tanggal_mulai'])
            ->diffInDays(Carbon::parse($data['tanggal_selesai'])) + 1;

        $overlap = Izin::where('id_pegawai', $id_pegawai)
            ->where(function ($q) use ($formatTanggalMulai, $formatTanggalSelesai) {
                $q->where('tanggal_mulai', '<=', $formatTanggalSelesai)
                    ->where('tanggal_selesai', '>=', $formatTanggalMulai);
            })
            ->exists();

        if ($overlap) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal izin bentrok dengan izin sebelumnya.'
            ], 422);
        }

        // CEK ABSENSI
        $absen = Absensi::where('id_pegawai', $id_pegawai)
            ->whereBetween('tanggal_absensi', [$data['tanggal_mulai'], $data['tanggal_selesai']])
            ->exists();

        if ($absen) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa izin karena sudah ada absensi.'
            ], 422);
        }

        // // UPLOAD SURAT IZIN
        if ($request->hasFile('suratizin')) {
            $file = $request->file('suratizin');
            $fileName = $id_pegawai . '_' . $id_proyek . '_' . now()->format('m.d.Y') . '_' . uniqid() . '.' . $file->extension();
            $path = $file->storeAs('SuratIzin', $fileName, 'public');
        } else {
            $path = null;
        }
        $izin = Izin::create([
            'id_pegawai'        => $id_pegawai,
            'id_proyek'         => $id_proyek,
            'keterangan_izin'   => $data['keterangan_izin'],
            'subjek_izin'       => $data['keterangan_izin'],
            'jenis_izin'        => $data['jenis_izin'],
            'surat_izin'         => $path,
            'tanggal_mulai'     => $formatTanggalMulai,
            'tanggal_selesai'   => $formatTanggalSelesai,
            'status_izin'       => 'proses',
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
            'data' => Izin::with('pegawai', 'proyek')->findOrFail($id)
        ], 200);
    }

    public function disetujui($id)
    {
        $izin = Izin::findOrFail($id);

        $izin->update([
            'status_izin' => 'disetujui'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data izin berhasil diperbarui.',
            'data' => $izin
        ], 200);
    }
    public function ditolak($id)
    {
        $izin = Izin::findOrFail($id);

        $izin->update([
            'status_izin' => 'ditolak'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data izin berhasil diperbarui.',
            'data' => $izin
        ], 200);
    }

    public function destroy($id)
    {
        $izin = Izin::findOrFail($id);

        if ($izin->suratizin) {
            Storage::disk('public')->delete($izin->suratizin);
        }

        $izin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data izin berhasil dihapus.'
        ], 200);
    }

    public function getAllIzinByUser()
{
    $user = auth('sanctum')->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User tidak terautentikasi'
        ], 401);
    }

    $id_pegawai = $user->id_pegawai;
    $thisMonth = Carbon::now();

    $data = Izin::with(['user', 'proyek'])
        ->where('id_pegawai', $id_pegawai)
        ->where('status_izin', 'disetujui')
        ->whereMonth('tanggal_mulai', $thisMonth->month)
        ->whereYear('tanggal_mulai', $thisMonth->year)
        ->orderBy('tanggal_mulai', 'desc')
        ->get();

    // if ($data->isEmpty()) {
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Belum ada izin yang disetujui pada bulan ini',
    //         'data' => []
    //     ], 200);
    // }

    return response()->json([
        'success' => true,
        'message' => 'Data izin berhasil diambil',
        'total' => $data->count(),
        'data' => $data
    ], 200);
}

}
