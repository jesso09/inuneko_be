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
        Schema::create('penitipans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('pet_id')->constrained('pets')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('pet_shop_id')->constrained('pet_shops')->onUpdate('cascade')->onDelete('cascade');
            $table->string('durasi');
            $table->integer('harga');
            $table->dateTime('mulai');
            $table->dateTime('selesai');
            $table->string('status');
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
        Schema::dropIfExists('penitipans');
    }
};
