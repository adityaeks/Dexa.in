<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nama')->constrained('hargas');
            $table->string('nomer_nota')->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->enum('status', ['Not started', 'Inprogress', 'Done']);
            $table->enum('prioritas', ['low', 'medium', 'urgent'])->nullable();
            $table->enum('status_payment', ['belum', 'DP', 'Lunas'])->nullable();
            $table->unsignedBigInteger('price');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
