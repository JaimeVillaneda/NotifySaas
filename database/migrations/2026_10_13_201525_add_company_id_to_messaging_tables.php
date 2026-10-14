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
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('message_templates', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('message_automations', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('communication_connection_id')->nullable()->after('message_template_id')->constrained()->nullOnDelete();
            $table->string('channel')->default('whatsapp')->after('name');
            $table->string('status')->default('pending')->after('is_active');
        });

        Schema::table('message_dispatches', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('communication_connection_id')->nullable()->after('message_template_id')->constrained()->nullOnDelete();
            $table->string('channel')->default('whatsapp')->after('phone');
            $table->unsignedInteger('attempts')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_dispatches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropConstrainedForeignId('communication_connection_id');
            $table->dropColumn(['channel', 'attempts']);
        });

        Schema::table('message_automations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropConstrainedForeignId('communication_connection_id');
            $table->dropColumn(['channel', 'status']);
        });

        Schema::table('message_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('people', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
