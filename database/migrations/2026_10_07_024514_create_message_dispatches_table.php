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
        Schema::create('message_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_automation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('message_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('person_id')->constrained()->cascadeOnDelete();
            $table->string('phone');
            $table->text('message');
            $table->string('status')->default('pending');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('response')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_dispatches');
    }
};
