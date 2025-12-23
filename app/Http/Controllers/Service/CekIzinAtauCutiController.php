<?php

namespace App\Http\Controllers\service;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use App\Models\Izin;
use Illuminate\Http\Request;

class CekIzinAtauCutiController extends Controller
{
public  function cekIzinAtauCuti($id_pegawai, $hariIni)
{
    if (Cuti::where('id_karyawan', $id_pegawai)
        ->where('status_cuti', 'disetujui')
        ->whereDate('tanggal_mulai', '<=', $hariIni)
        ->whereDate('tanggal_selesai', '>=', $hariIni)
        ->exists()) {

        return [
            'status' => true,
            'message' => 'Pegawai sedang cuti dan tidak dapat melakukan absensi'
        ];
    }

    if (Izin::where('id_pegawai', $id_pegawai)
        ->where('status_izin', 'disetujui')
        ->whereDate('tanggal_mulai', '<=', $hariIni)
        ->whereDate('tanggal_selesai', '>=', $hariIni)
        ->exists()) {

        return [
            'status' => true,
            'message' => 'Pegawai sedang izin dan tidak dapat melakukan absensi'
        ];
    }

    return [
        'status' => false,
        'message' => null
    ];
}
}
