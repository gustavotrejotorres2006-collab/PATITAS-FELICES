<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('espacios_clinicos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('tipo');
            $table->string('estado')->default('disponible');
            $table->boolean('activo')->default(true);
            $table->dateTime('desinfectado_at')->nullable();
            $table->foreignId('desinfectado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['tipo', 'estado', 'activo']);
        });

        Schema::create('cirugias', function (Blueprint $table): void {
            $table->id();
            $table->string('folio')->unique();
            $table->foreignId('atencion_id')->constrained('atenciones')->cascadeOnDelete();
            $table->foreignId('espacio_id')->constrained('espacios_clinicos')->restrictOnDelete();
            $table->foreignId('cirujano_id')->constrained('profesionales')->restrictOnDelete();
            $table->foreignId('anestesiologo_id')->constrained('profesionales')->restrictOnDelete();
            $table->foreignId('propietario_id')->constrained('propietarios')->restrictOnDelete();
            $table->dateTime('programada_at');
            $table->unsignedSmallInteger('duracion_minutos');
            $table->string('estado')->default('programada');
            $table->dateTime('consentimiento_firmado_at');
            $table->foreignId('consentimiento_verificado_por')->constrained('users')->restrictOnDelete();
            $table->text('observaciones')->nullable();
            $table->dateTime('completada_at')->nullable();
            $table->foreignId('completada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['espacio_id', 'estado', 'programada_at']);
            $table->index(['cirujano_id', 'estado', 'programada_at']);
            $table->index(['anestesiologo_id', 'estado', 'programada_at']);
        });

        Schema::create('cirugia_insumos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cirugia_id')->constrained('cirugias')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->foreignId('lote_id')->nullable()->constrained('lotes_inventario')->nullOnDelete();
            $table->foreignId('movimiento_id')->nullable()->constrained('movimientos_inventario')->nullOnDelete();
            $table->string('numero_lote')->nullable();
            $table->decimal('cantidad', 12, 3);
            $table->decimal('costo_unitario', 12, 4);
            $table->timestamps();
            $table->index(['cirugia_id', 'producto_id']);
        });

        Schema::create('hospitalizaciones', function (Blueprint $table): void {
            $table->id();
            $table->string('folio')->unique();
            $table->foreignId('atencion_id')->constrained('atenciones')->cascadeOnDelete();
            $table->foreignId('espacio_id')->constrained('espacios_clinicos')->restrictOnDelete();
            $table->dateTime('ingresada_at');
            $table->foreignId('ingresada_por')->constrained('users')->restrictOnDelete();
            $table->string('estado')->default('internada');
            $table->dateTime('alta_at')->nullable();
            $table->foreignId('dada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['estado', 'ingresada_at']);
        });

        Schema::create('monitoreos_hospitalarios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hospitalizacion_id')->constrained('hospitalizaciones')->cascadeOnDelete();
            $table->foreignId('registrada_por')->constrained('users')->restrictOnDelete();
            $table->dateTime('registrada_at');
            $table->decimal('temperatura_c', 5, 2)->nullable();
            $table->unsignedSmallInteger('frecuencia_cardiaca')->nullable();
            $table->unsignedSmallInteger('frecuencia_respiratoria')->nullable();
            $table->text('tratamiento');
            $table->longText('nota_progreso');
            $table->timestamps();
            $table->index(['hospitalizacion_id', 'registrada_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoreos_hospitalarios');
        Schema::dropIfExists('hospitalizaciones');
        Schema::dropIfExists('cirugia_insumos');
        Schema::dropIfExists('cirugias');
        Schema::dropIfExists('espacios_clinicos');
    }
};
