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
        Schema::create('evolution_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Principal');
            $table->string('base_url')->nullable();
            $table->string('instance_name')->nullable();
            $table->text('api_key')->nullable();
            $table->string('webhook_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('options')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evolution_settings');
    }
};
