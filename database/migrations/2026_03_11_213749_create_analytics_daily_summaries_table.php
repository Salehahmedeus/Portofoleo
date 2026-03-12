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
        Schema::create('analytics_daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('summary_date');
            $table->string('event_type');
            $table->text('page_url')->nullable();
            $table->string('device_type')->nullable();
            $table->string('country', 2)->nullable();
            $table->unsignedBigInteger('events_count');
            $table->timestamps();

            $table->unique([
                'summary_date',
                'event_type',
                'page_url',
                'device_type',
                'country',
            ], 'analytics_daily_summaries_unique');
            $table->index('summary_date');
            $table->index(['event_type', 'summary_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_daily_summaries');
    }
};
