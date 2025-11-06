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
            // AI Embedding 相關
            $table->text('embedding')->nullable()->after('body')
                ->comment('AI 向量嵌入 (JSON)');
            $table->string('embedding_model', 50)->nullable()->after('embedding')
                ->comment('嵌入模型名稱');
            $table->integer('embedding_updated_at')->nullable()->after('embedding_model')
                ->comment('嵌入更新時間');
            
            // 統計相關
            $table->integer('view_count')->default(0)->after('show')
                ->comment('查看次數');
            $table->integer('helpful_count')->default(0)->after('view_count')
                ->comment('有幫助次數');
            $table->integer('unhelpful_count')->default(0)->after('helpful_count')
                ->comment('無幫助次數');
            
            // 標籤和關聯
            $table->json('tags')->nullable()->after('unhelpful_count')
                ->comment('文章標籤');
            $table->json('related_ids')->nullable()->after('tags')
                ->comment('相關文章 ID 列表');
            
            // 索引
            $table->index('view_count', 'idx_view_count');
            $table->index('helpful_count', 'idx_helpful_count');
        });

        // 創建搜尋日誌表
        Schema::create('v2_knowledge_search_log', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable()->comment('使用者 ID');
            $table->text('query')->comment('搜尋查詢');
            $table->enum('query_type', ['keyword', 'semantic', 'ai_chat'])
                ->default('keyword')->comment('查詢類型');
            $table->integer('results_count')->default(0)->comment('結果數量');
            $table->integer('clicked_id')->nullable()->comment('點擊的文章 ID');
            $table->boolean('is_helpful')->nullable()->comment('是否有幫助');
            $table->integer('created_at')->comment('創建時間');
            
            $table->index('user_id', 'idx_user_id');
            $table->index('created_at', 'idx_created_at');
            $table->index('query_type', 'idx_query_type');
        });

        // 創建 AI 對話記錄表
        Schema::create('v2_knowledge_ai_chat', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable()->comment('使用者 ID');
            $table->string('session_id', 64)->comment('會話 ID');
            $table->text('question')->comment('使用者問題');
            $table->text('answer')->comment('AI 回答');
            $table->json('context')->nullable()->comment('上下文（引用的文章）');
            $table->string('model', 50)->nullable()->comment('使用的模型');
            $table->integer('tokens_used')->nullable()->comment('使用的 token 數');
            $table->boolean('is_helpful')->nullable()->comment('是否有幫助');
            $table->integer('created_at')->comment('創建時間');
            
            $table->index('user_id', 'idx_user_id');
            $table->index('session_id', 'idx_session_id');
            $table->index('created_at', 'idx_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v2_knowledge', function (Blueprint $table) {
            $table->dropIndex('idx_view_count');
            $table->dropIndex('idx_helpful_count');
            
            $table->dropColumn([
                'embedding',
                'embedding_model',
                'embedding_updated_at',
                'view_count',
                'helpful_count',
                'unhelpful_count',
                'tags',
                'related_ids'
            ]);
        });

        Schema::dropIfExists('v2_knowledge_search_log');
        Schema::dropIfExists('v2_knowledge_ai_chat');
    }
};
