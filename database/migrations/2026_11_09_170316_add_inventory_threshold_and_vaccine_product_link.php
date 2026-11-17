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
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('stock_optimo', 12, 3)->nullable();
        });

        Schema::table('catalogo_vacunas', function (Blueprint $table) {
            $table->foreignId('producto_id')
                ->nullable()
                ->unique()
                ->constrained('productos')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogo_vacunas', function (Blueprint $table) {
            $table->dropUnique('catalogo_vacunas_producto_id_unique');
            $table->dropConstrainedForeignId('producto_id');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('stock_optimo');
        });
    }
};
