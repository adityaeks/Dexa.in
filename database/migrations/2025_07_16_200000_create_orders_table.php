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
            $table->json('nama')->nullable();
            $table->string('nomer_nota')->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->string('customer_code')->nullable(); // kode customer
            $table->enum('status', ['Not started', 'Inprogress', 'Done']);
            $table->enum('prioritas', ['low', 'medium', 'urgent'])->nullable();
            $table->enum('status_payment', ['belum', 'DP', 'Lunas'])->nullable();
            $table->unsignedInteger('qty')->nullable();
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('amt_reff')->nullable();
            $table->unsignedBigInteger('price_dexain')->nullable();
            $table->unsignedBigInteger('price_akademisi')->nullable();
            $table->json('price_akademisi2')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('contact')->nullable();
            $table->json('akademisi_id')->nullable();
            $table->json('file_tambahan')->nullable();
            $table->json('link_tambahan')->nullable();
            $table->text('note')->nullable();
            $table->json('payment_ids')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
