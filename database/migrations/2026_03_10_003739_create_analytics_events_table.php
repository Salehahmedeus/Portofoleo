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
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->json('event_data')->nullable();
            $table->text('page_url');
            $table->text('referrer')->nullable();
            $table->string('device_type')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('ip_address')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();

            $table->index(['event_type', 'created_at']);
            $table->index(['session_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
