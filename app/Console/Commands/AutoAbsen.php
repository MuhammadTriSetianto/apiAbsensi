<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cuti;
use App\Models\Izin;
use App\Models\Pegawai;
use App\Models\Absensi;
use App\Models\UserProyeks;
use Carbon\Carbon;

class AutoAbsen extends Command
{
    protected $signature = 'app:auto-absen';
    protected $description = 'Generate absensi otomatis berdasarkan cuti dan izin';

    public function handle()
    {
        $tanggal = Carbon::today();

        $this->info("Auto absensi untuk tanggal {$tanggal}");

        $pegawaiList = Pegawai::all();

        foreach ($pegawaiList as $pegawai) {


            $sudahAbsen = Absensi::where('id_pegawai', $pegawai->id_pegawai)
                ->whereDate('tanggal_absensi', $tanggal)
                ->exists();

            $proyek = UserProyeks::where('id_pegawai', $pegawai->id_pegawai)->firstOrFail();

            if ($sudahAbsen) {
                continue;
            }

            $cuti = Cuti::where('id_karyawan', $pegawai->id_pegawai)
                ->where('status_cuti', 'disetujui')
                ->whereDate('tanggal_mulai', '<=', $tanggal)
                ->whereDate('tanggal_selesai', '>=', $tanggal)
                ->exists();

            if ($cuti) {
                Absensi::create([
                    'id_pegawai' => $pegawai->id_pegawai,
                    'id_proyek' => $proyek->id_proyek,
                    'tanggal_absensi' => $tanggal,
                    'jam_masuk' => null,
                    'jam_keluar' => null,
                    'keterangan_absensi' => 'cuti',
                ]);

                $this->info("Pegawai {$pegawai->id_pegawai} → CUTI");
                continue;
            }

            // CEK IZIN
            $izin = Izin::where('id_pegawai', $pegawai->id_pegawai)
                ->where('status_izin', 'disetujui')
                ->whereDate('tanggal_mulai', '<=', $tanggal)
                ->whereDate('tanggal_selesai', '>=', $tanggal)
                ->exists();

            if ($izin) {
                Absensi::create([
                    'id_pegawai' => $pegawai->id_pegawai,
                    'tanggal_absensi' => $tanggal,
                    'id_proyek' => $proyek->id_proyek,
                    'jam_masuk' => null,
                    'jam_keluar' => null,
                    'keterangan_absensi' => 'izin',
                ]);

                $this->info("Pegawai {$pegawai->id_pegawai} → IZIN");
                continue;
            }

            Absensi::create([
                'id_pegawai' => $pegawai->id_pegawai,
                'id_proyek' => $proyek->id_proyek,
                'tanggal_absensi' => $tanggal,
                'jam_masuk' => null,
                'jam_keluar' => null,
                'keterangan_absensi' => 'alpha',
            ]);

            $this->info("Pegawai {$pegawai->id_pegawai} → ALPHA");
        }

        $this->info('Auto absensi selesai.');
    }
}
