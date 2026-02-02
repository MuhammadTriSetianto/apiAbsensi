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
        Schema::create('absensis', function (Blueprint $table) {
            $table->id("id_absensi")->primary();
            $table->string("id_pegawai");          //FK(freinger key from table users)
            $table->unsignedBigInteger("id_proyek");       //FK(freinger key from table proyeks)
            $table->date("tanggal_absensi");
            $table->time("jam_masuk")->nullable();
            $table->time("jam_pulang")->nullable();
            $table->string("keterangan_absensi");
            $table->timestamps();

            $table->foreign('id_proyek')->references("id_proyek")->on("proyeks")->onDelete('cascade');
        
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
