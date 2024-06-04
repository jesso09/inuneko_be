<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aktivitas_penitipans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penitipan_id')->constrained('penitipans')->onUpdate('cascade')->onDelete('cascade');
            $table->string('foto')->nullable();
            $table->string('video')->nullable();
            $table->string('judul_aktivitas');
            $table->dateTime('waktu_aktivitas');
            $table->text('keterangan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aktivitas_penitipans');
    }
};
