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
        Schema::table('v2_user', function (Blueprint $table) {
            // Logto user unique identifier (sub claim from OIDC)
            $table->string('logto_sub', 255)->nullable()->unique()->after('uuid')
                ->comment('Logto user ID (sub claim)');
            
            // Authentication provider identifier
            $table->string('auth_provider', 20)->default('local')->after('password_salt')
                ->comment('Authentication provider: local, logto');
            
            // Add indexes for better query performance
            $table->index('logto_sub', 'idx_logto_sub');
            $table->index('auth_provider', 'idx_auth_provider');
        });

        // Make password nullable for Logto users
        Schema::table('v2_user', function (Blueprint $table) {
            $table->string('password', 64)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_user', function (Blueprint $table) {
            $table->dropIndex('idx_logto_sub');
            $table->dropIndex('idx_auth_provider');
            $table->dropColumn(['logto_sub', 'auth_provider']);
        });

        // Restore password to non-nullable (if needed)
        Schema::table('v2_user', function (Blueprint $table) {
            $table->string('password', 64)->nullable(false)->change();
        });
    }
};
