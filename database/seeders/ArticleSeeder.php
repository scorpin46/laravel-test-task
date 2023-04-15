<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Rubric;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rubrics = Rubric::query()
            ->has('children', '=', 0)
            ->get();

        Article::factory(100)->create()
            ->each(function(Article $article) use ($rubrics) {
                $article->rubrics()->attach($rubrics->random(rand(1, 2)));
            });
    }
}
