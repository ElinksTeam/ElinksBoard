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
            // Store Logto roles as JSON array
            $table->json('logto_roles')->nullable()->after('logto_sub')
                ->comment('Logto user roles from RBAC');
            
            // Store Logto organizations (for future use)
            $table->json('logto_organizations')->nullable()->after('logto_roles')
                ->comment('Logto organizations the user belongs to');
            
            // Last time roles were synced from Logto
            $table->timestamp('logto_roles_synced_at')->nullable()->after('logto_organizations')
                ->comment('Last time roles were synced from Logto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_user', function (Blueprint $table) {
            $table->dropColumn([
                'logto_roles',
                'logto_organizations',
                'logto_roles_synced_at'
            ]);
        });
    }
};
