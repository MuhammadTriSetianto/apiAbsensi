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
        Schema::create('pegawais', function (Blueprint $table) { 
            $table->id('id');
            $table->string('id_pegawai',10)->unique();
            $table->string('name',50);
            $table->string('email')->unique();
            $table->string('no_hp')->nullable();
            $table->string('alamat')->nullable();
            $table->unsignedBigInteger('id_role')->default(3);
            $table->string('password');
            $table->string('jabatan')->nullable();
            $table->timestamps();
    
        });

        Schema::table('absensis', function (Blueprint $table) {
            $table ->foreign('id_pegawai')->references('id_pegawai')->on('pegawais')->onDelete('cascade');
        });

        Schema::table('user_proyeks', function (Blueprint $table) {
            $table ->foreign('id_pegawai')->references('id_pegawai')->on('pegawais')->onDelete('cascade');
        });

        Schema::table('izins', function (Blueprint $table) {
            $table ->foreign('id_pegawai')->references('id_pegawai')->on('pegawais')->onDelete('cascade');
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
