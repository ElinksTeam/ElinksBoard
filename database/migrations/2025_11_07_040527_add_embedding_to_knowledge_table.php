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
        Schema::table('v2_knowledge', function (Blueprint $table) {
            $table->text('embedding')->nullable()->after('body');
            $table->timestamp('embedding_generated_at')->nullable()->after('embedding');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_knowledge', function (Blueprint $table) {
            $table->dropColumn(['embedding', 'embedding_generated_at']);
        });
    }
};
