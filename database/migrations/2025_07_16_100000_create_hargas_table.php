<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hargas', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('tingkat', ['low', 'medium', 'high']);
            $table->unsignedBigInteger('harga');
            $table->enum('tipe', ['pendidikan', 'instansi']);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hargas');
    }
};
