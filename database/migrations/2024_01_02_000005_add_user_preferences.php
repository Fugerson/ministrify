<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('theme')->default('auto')->after('remember_token'); // light, dark, auto
            $table->json('preferences')->nullable()->after('theme');
            $table->boolean('onboarding_completed')->default(false)->after('preferences');
        });

        // Message templates for mass messaging
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('content');
            $table->string('type')->default('telegram'); // telegram, sms, email
            $table->timestamps();
        });

        // Message history
        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // telegram, sms, email
            $table->text('content');
            $table->json('recipients')->nullable(); // array of person_ids or group info
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->timestamps();
        });

        // Communication history per person
        Schema::create('person_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type'); // telegram, phone, meeting, note
            $table->string('direction')->default('outgoing'); // incoming, outgoing
            $table->text('content')->nullable();
            $table->timestamp('communicated_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_communications');
        Schema::dropIfExists('message_logs');
        Schema::dropIfExists('message_templates');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['theme', 'preferences', 'onboarding_completed']);
        });
    }
};
