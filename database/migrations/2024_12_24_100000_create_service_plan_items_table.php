<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add service planning fields to events
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_service')->default(false)->after('notes');
            $table->string('service_type')->nullable()->after('is_service');
        });

        // Create service plan items table
        Schema::create('service_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', [
                'worship',
                'sermon',
                'announcement',
                'prayer',
                'offering',
                'testimony',
                'baptism',
                'communion',
                'child_blessing',
                'special',
                'other'
            ])->default('other');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->foreignId('responsible_id')->nullable()->constrained('people')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['planned', 'confirmed', 'completed'])->default('planned');
            $table->timestamps();

            $table->index(['event_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_plan_items');

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['is_service', 'service_type']);
        });
    }
};
