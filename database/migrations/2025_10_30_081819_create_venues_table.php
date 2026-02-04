<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // 場所名
            $table->decimal('latitude', 10, 7)->nullable(); // 緯度
            $table->decimal('longitude', 10, 7)->nullable(); // 経度
            $table->timestamps();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('venue_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['venue_id']);
            $table->dropColumn('venue_id');
        });

        Schema::dropIfExists('venues');
    }
};
