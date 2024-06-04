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
        Schema::create('layanan_vets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vet_id')->constrained('vets')->onUpdate('cascade')->onDelete('cascade');
            $table->string('nama_layanan');
            $table->integer('harga');
            $table->integer('harga_per_jarak');
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
        Schema::dropIfExists('layanan_vets');
    }
};
