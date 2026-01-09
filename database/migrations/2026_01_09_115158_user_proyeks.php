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
        Schema::create('user_proyeks', function (Blueprint $table) {
            $table->id('id_user_proyek');
            $table->string("id_pegawai");          //FK(freinger key from table users)
            $table->unsignedBigInteger("id_proyek");        //FK(freinger key from table proyeks)
            $table->string("jabatan", 20);
            $table->timestamps();

            $table->foreign('id_proyek')->references('id_proyek')->on('proyeks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_proyeks');
    }
};
