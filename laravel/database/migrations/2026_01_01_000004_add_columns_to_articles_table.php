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
        Schema::create('article_article', function (Blueprint $table) {
            $table->foreignId('article_source')->constrained('articles');
            $table->foreignId('article_target')->constrained('articles');
            $table->primary(['article_source', 'article_target']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_article');
    }
};
