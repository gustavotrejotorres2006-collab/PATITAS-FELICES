<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('roles', function(Blueprint $t){$t->id();$t->string('nombre')->unique();$t->string('descripcion')->nullable();$t->boolean('activo')->default(true);$t->timestamps();});
  Schema::create('permissions', function(Blueprint $t){$t->id();$t->string('clave')->unique();$t->string('nombre');$t->string('modulo')->nullable();$t->timestamps();});
  Schema::create('role_permissions', function(Blueprint $t){$t->id();$t->foreignId('role_id')->constrained('roles')->cascadeOnDelete();$t->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();$t->timestamps();$t->unique(['role_id','permission_id']);});
 }
 public function down(): void {Schema::dropIfExists('role_permissions');Schema::dropIfExists('permissions');Schema::dropIfExists('roles');}
};