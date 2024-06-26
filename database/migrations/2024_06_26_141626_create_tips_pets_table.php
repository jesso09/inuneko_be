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
        Schema::create('tips_pets', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('jenis_pet');
            $table->string('ras_pet')->nullable();
            $table->string('tips_pict')->nullable();
            $table->longText('tips_text');
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
        Schema::dropIfExists('tips_pets');
    }
};
