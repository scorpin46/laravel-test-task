<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function(Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->string('intro_text', SchemaBuilder::$defaultStringLength * 2)->nullable();
            $table->text('text')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->timestamps();

            $table->fullText(['name', 'text']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
