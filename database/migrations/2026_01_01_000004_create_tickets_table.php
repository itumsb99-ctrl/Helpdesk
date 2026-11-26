<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number',40)->unique();
            $table->string('title',180);
            $table->text('description');
            $table->string('priority',20)->default('medium');
            $table->string('status',30)->default('open');
            $table->string('location',180);
            $table->string('unit',120);
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('response_due_at')->nullable();
            $table->timestamp('resolution_due_at')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamps();
            $table->index(['status','priority']);
            $table->index(['technician_id','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('tickets'); }
};
