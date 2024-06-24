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
        Schema::create('house_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('pet_id')->constrained('pets')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('vet_id')->constrained('vets')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('layanan_vets')->onUpdate('cascade')->onDelete('cascade');
            $table->string('housecall_order_id');
            $table->string('status');
            $table->dateTime('mulai');
            $table->dateTime('selesai');
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
        Schema::dropIfExists('house_calls');
    }
};
