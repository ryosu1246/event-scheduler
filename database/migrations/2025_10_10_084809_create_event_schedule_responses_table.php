<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_schedule_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_schedule_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['yes', 'no', 'pending'])->default('pending');
            $table->timestamps();

            $table->unique(['event_schedule_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_schedule_responses');
    }
};
