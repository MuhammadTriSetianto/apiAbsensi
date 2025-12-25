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
        Schema::create('izins', function (Blueprint $table) {
            $table->id("id_izin")->primary();
            $table->string("id_pegawai");          //FK(freinger key from table users)
            $table->unsignedBigInteger("id_proyek");        //FK(freinger key from table proyeks) 
            $table->string("subjek_izin");
            $table->string("keterangan_izin");
            $table->enum("jenis_izin", ["sakit", "cuti", "lainnya"]);
            $table->string("surat_izin");
            $table->date("tanggal_mulai")->nullable();
            $table->date("tanggal_selesai")->nullable();
            $table->enum("status_izin", ["proses", "disetujui", "ditolak"]);
            $table->timestamps();

            $table->foreign('id_proyek')->references("id_proyek")->on("proyeks")->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('izins');
    }
};
