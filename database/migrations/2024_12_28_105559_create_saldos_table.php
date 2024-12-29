<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saldos', function (Blueprint $table) {
            $table->string('nim')->primary(); // NIM sebagai primary key
            $table->decimal('saldo', 15, 2)->default(0); // Saldo dengan presisi 15 digit dan 2 angka desimal, default 0
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldos');
    }
};