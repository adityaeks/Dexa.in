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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('akademisi_id');
            $table->string('akademisi_name');
            $table->bigInteger('price_order');
            $table->bigInteger('price');
            $table->bigInteger('amt_reff');
            $table->enum('status', ['belum', 'dp', 'lunas'])->default('belum');
            $table->json('bukti_pembayaran')->nullable();
            $table->string('tr_code');
            $table->unsignedInteger('seq')->nullable();
            $table->timestamps();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
