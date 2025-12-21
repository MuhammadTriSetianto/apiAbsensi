<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id('id_role');
            $table->string('nama_role', 50);
            $table->timestamps();
        });
        
        //table pegawais
        Schema :: table ('pegawais', function (Blueprint $table) {
            $table -> foreign('id_role')->references('id_role')->on('roles')->onDelete('cascade');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
