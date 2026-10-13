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
        Schema::create('message_automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_template_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('frequency')->default('once');
            $table->time('send_time')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('interval_value')->nullable();
            $table->string('interval_unit')->nullable();
            $table->json('week_days')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_automations');
    }
};
