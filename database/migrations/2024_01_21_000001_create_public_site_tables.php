<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add public site settings to churches
        Schema::table('churches', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('name');
            $table->boolean('public_site_enabled')->default(false)->after('slug');
            $table->text('public_description')->nullable()->after('public_site_enabled');
            $table->string('public_email')->nullable();
            $table->string('public_phone')->nullable();
            $table->string('website_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->text('service_times')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('pastor_name')->nullable();
            $table->string('pastor_photo')->nullable();
            $table->text('pastor_message')->nullable();
        });

        // Add public visibility to ministries
        Schema::table('ministries', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('monthly_budget');
            $table->string('slug')->nullable()->after('is_public');
            $table->text('public_description')->nullable()->after('slug');
            $table->string('cover_image')->nullable()->after('public_description');
            $table->boolean('allow_registrations')->default(false)->after('cover_image');
        });

        // Add public visibility to events
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('parent_event_id');
            $table->boolean('allow_registration')->default(false)->after('is_public');
            $table->integer('registration_limit')->nullable()->after('allow_registration');
            $table->timestamp('registration_deadline')->nullable()->after('registration_limit');
            $table->text('public_description')->nullable()->after('registration_deadline');
            $table->string('location')->nullable()->after('public_description');
            $table->string('cover_image')->nullable()->after('location');
        });

        // Add public visibility to groups
        Schema::table('groups', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('church_id');
            $table->string('slug')->nullable()->after('is_public');
            $table->text('public_description')->nullable()->after('slug');
            $table->string('cover_image')->nullable()->after('public_description');
            $table->boolean('allow_join_requests')->default(false)->after('cover_image');
            $table->string('meeting_schedule')->nullable()->after('allow_join_requests');
        });

        // Event registrations
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->integer('guests')->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'attended'])->default('pending');
            $table->string('confirmation_token')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        // Donations
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->foreignId('person_id')->nullable()->constrained()->onDelete('set null');
            $table->string('donor_name')->nullable();
            $table->string('donor_email')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('UAH');
            $table->enum('type', ['one_time', 'recurring'])->default('one_time');
            $table->string('purpose')->nullable();
            $table->foreignId('ministry_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->timestamps();
        });

        // Donation campaigns/funds
        Schema::create('donation_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('goal_amount', 10, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('cover_image')->nullable();
            $table->timestamps();
        });

        // Group join requests
        Schema::create('group_join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });

        // Ministry join requests
        Schema::create('ministry_join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ministry_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('skills')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ministry_join_requests');
        Schema::dropIfExists('group_join_requests');
        Schema::dropIfExists('donation_campaigns');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('event_registrations');

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['is_public', 'slug', 'public_description', 'cover_image', 'allow_join_requests', 'meeting_schedule']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['is_public', 'allow_registration', 'registration_limit', 'registration_deadline', 'public_description', 'location', 'cover_image']);
        });

        Schema::table('ministries', function (Blueprint $table) {
            $table->dropColumn(['is_public', 'slug', 'public_description', 'cover_image', 'allow_registrations']);
        });

        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn([
                'slug', 'public_site_enabled', 'public_description', 'public_email', 'public_phone',
                'website_url', 'facebook_url', 'instagram_url', 'youtube_url', 'service_times',
                'cover_image', 'pastor_name', 'pastor_photo', 'pastor_message'
            ]);
        });
    }
};
