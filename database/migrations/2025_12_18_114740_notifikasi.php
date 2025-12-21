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
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->string('id_user'); //pembaca notifikasi
            $table->string('id_pengirim');
            $table->unsignedBigInteger('id_proyek');
            $table->string('judul');
            $table->string('isi');
            $table->enum('status',['dibaca', 'belum_dibaca']);
            $table->timestamps();


            $table->foreign('id_user')->references('id_pegawai')->on('pegawais')->onDelete('cascade');
            $table->foreign('id_pengirim')->references('id_pegawai')->on('pegawais')->onDelete('cascade');
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
