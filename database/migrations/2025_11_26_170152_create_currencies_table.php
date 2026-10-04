<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // USD, EUR, etc.
            $table->string('name'); // US Dollar, Euro, etc.
            $table->string('symbol', 10); // $, €, £, etc.
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('decimal_places')->default(2);
            $table->timestamps();

            $table->index(['code', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
