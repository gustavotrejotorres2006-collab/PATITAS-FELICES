<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('configuracion_clinica', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->longText('valor')->nullable();
            $table->timestamps();
        });

        Schema::create('folios', function (Blueprint $table) {
            $table->id();
            $table->string('modulo')->unique();
            $table->string('prefijo');
            $table->unsignedBigInteger('ultimo_numero')->default(0);
            $table->unsignedInteger('longitud')->default(6);
            $table->timestamps();
        });

        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo');
            $table->string('titulo');
            $table->text('mensaje');
            $table->dateTime('programada_at')->nullable();
            $table->dateTime('leida_at')->nullable();
            $table->string('canal')->default('interno');
            $table->string('estado')->default('pendiente');
            $table->json('datos')->nullable();
            $table->timestamps();
        });

        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion');
            $table->string('entidad');
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['entidad', 'entidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('folios');
        Schema::dropIfExists('configuracion_clinica');
    }
};
