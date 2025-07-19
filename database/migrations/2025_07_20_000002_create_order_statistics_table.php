<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('period')->unique(); // format: YYYY-MM
            $table->unsignedBigInteger('total_orders')->default(0);
            $table->unsignedBigInteger('done_orders')->default(0);
            $table->unsignedBigInteger('open_orders')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_statistics');
    }
};
