<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->text('privatbank_merchant_id')->nullable();
            $table->text('privatbank_password')->nullable();
            $table->string('privatbank_card_number')->nullable();
            $table->boolean('privatbank_auto_sync')->default(false);
            $table->timestamp('privatbank_last_sync')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn([
                'privatbank_merchant_id',
                'privatbank_password',
                'privatbank_card_number',
                'privatbank_auto_sync',
                'privatbank_last_sync',
            ]);
        });
    }
};
