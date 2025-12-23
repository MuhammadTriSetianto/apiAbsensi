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
        Schema::create('foto_absensis', function (Blueprint $table) {
            $table->id("id_foto_absensi")->primary();
            $table->unsignedBigInteger("id_absensi");          //FK(freinger key from table absensis)
            $table->string("foto_absensi");
            $table->decimal("latitude");
            $table->decimal("longitude");
            $table->timestamps();
            
            $table->foreign('id_absensi')->references("id_absensi")->on("absensis")->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foto_absensis');
    }
};
