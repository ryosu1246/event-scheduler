<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('event_schedule_responses');
    }

    public function down(): void
    {
        Schema::create('event_schedule_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_schedule_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('status', ['yes', 'maybe', 'no']);
            $table->timestamps();
        });
    }
};
