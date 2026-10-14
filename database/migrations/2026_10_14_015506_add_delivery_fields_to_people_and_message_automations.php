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
        Schema::table('people', function (Blueprint $table) {
            $table->string('preferred_channel')->default('whatsapp')->after('email');
            $table->string('telegram_chat_id')->nullable()->after('preferred_channel');
        });

        Schema::table('message_automations', function (Blueprint $table) {
            $table->foreignId('person_id')->nullable()->after('communication_connection_id')->constrained('people')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_automations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('person_id');
        });

        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn(['preferred_channel', 'telegram_chat_id']);
        });
    }
};
