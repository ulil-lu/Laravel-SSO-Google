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
    // Only add columns that don't already exist on the users table.
    // Avoid adding `id()` here because this migration is altering an existing table.
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'google_id')) {
            $table->string('google_id')->nullable()->unique()->after('id');
        }

        if (!Schema::hasColumn('users', 'name')) {
            $table->string('name')->nullable()->after('google_id');
        }

        if (!Schema::hasColumn('users', 'email')) {
            $table->string('email')->nullable()->unique()->after('name');
        }

        if (!Schema::hasColumn('users', 'remember_token')) {
            $table->rememberToken();
        }

        if (!Schema::hasColumn('users', 'created_at')) {
            $table->timestamps();
        }
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_id');
        });
    }
};
