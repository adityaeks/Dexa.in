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
        Schema::create('akademisis', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nomor');
            $table->string('jurusan');
            $table->string('asal_kampus');
            $table->string('rekening')->nullable();
            $table->json('minat')->nullable(); // Untuk menyimpan tags
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akademisis');
    }
};
