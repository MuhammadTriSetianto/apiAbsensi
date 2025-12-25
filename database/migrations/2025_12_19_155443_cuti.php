<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cutis', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('id_karyawan');
            $table->string('id_cuti')->unique();
            $table->unsignedBigInteger('id_proyek');
            $table->string('subjek_cuti');
            $table->string('keterangan_cuti');
            $table->string('surat_cuti');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status_cuti', ['proses', 'disetujui', 'ditolak']);
            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('id_karyawan')->references('id_pegawai')->on('pegawais')->onDelete('cascade');
            $table->foreign('id_proyek')->references('id_proyek')->on('proyeks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
