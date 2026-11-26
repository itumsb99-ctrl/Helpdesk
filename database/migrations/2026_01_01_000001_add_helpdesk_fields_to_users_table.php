<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('pegawai')->after('password');
            $table->string('unit')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('unit');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role','unit','is_active']);
        });
    }
};
