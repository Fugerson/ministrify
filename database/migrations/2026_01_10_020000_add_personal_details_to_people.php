<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female'])->nullable()->after('email');
            $table->enum('marital_status', ['single', 'married', 'widowed', 'divorced'])->nullable()->after('gender');
            $table->date('anniversary')->nullable()->after('baptism_date');
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn(['gender', 'marital_status', 'anniversary']);
        });
    }
};
