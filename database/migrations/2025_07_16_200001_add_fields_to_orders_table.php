<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('price_dexain')->nullable()->after('price');
            $table->unsignedBigInteger('price_akademisi')->nullable()->after('price_dexain');
            $table->date('due_days')->nullable();
            $table->string('contact')->nullable();
            $table->foreignId('akademisi_id')->nullable()->constrained('akademisis');
            $table->json('file_tambahan')->nullable();
            $table->json('link_tambahan')->nullable();
            $table->string('bukti_payment')->nullable();
            $table->text('note')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'price_dexain',
                'price_akademisi',
                'due_days',
                'contact',
                'akademisi_id',
                'file_tambahan',
                'link_tambahan',
                'bukti_payment',
                'note',
            ]);
        });
    }
};
