<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rubric_article', function(Blueprint $table) {
            $table->foreignId('article_id');
            $table->integer('rubric_id', false, true);

            $table->foreign('rubric_id')
                ->references('id')
                ->on('rubrics')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('article_id')
                ->references('id')
                ->on('articles')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubric_article');
    }
};
