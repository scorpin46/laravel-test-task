<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'         => ($name = $this->faker->unique()->name),
            'slug'         => Str::slug($name),
            'text'         => ($text = $this->faker->realText(1500)),
            'intro_text'   => Str::limit($text, SchemaBuilder::$defaultStringLength),
            'published_at' => $this->faker->dateTime,
        ];
    }
}
