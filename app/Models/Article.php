<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Article extends BaseModel
{
    use HasFactory;

    const SCENARIO_UPDATE = 'update';

    /**
     * @var string[]
     */
    protected array $dates = [
        'created_at',
        'updated_at',
        'published_at',
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'slug'         => null,
        'name'         => null,
        'intro_text'   => null,
        'text'         => null,
        'published_at' => null,
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'slug'         => 'string',
        'name'         => 'string',
        'intro_text'   => 'string',
        'text'         => 'string',
        'published_at' => 'date',
    ];

    /**
     * @var string[]
     */
    protected array $rules = [
        'slug'         => 'string|max:255',
        'name'         => 'string|max:255',
        'intro_text'   => 'string|max:510',
        'text'         => 'string',
        'published_at' => 'date',
        'rubrics.*'    => 'integer',
    ];

    /**
     * {@inheritDoc}
     */
    protected $scenarios = [
        self::SCENARIO_DEFAULT => [
            '!slug',
            '!name',
            '!intro_text',
            '!text',
            '!published_at',
        ],
        self::SCENARIO_UPDATE  => [
            '!slug',
            'name',
            '!intro_text',
            'text',
            'rubrics',
        ],
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function rubrics(): BelongsToMany
    {
        return $this->belongsToMany(Rubric::class, 'rubric_article');
    }

    /**
     * Accessor. Page url
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function link(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => route('articles.show', ['id' => $this->id]),
        );
    }

    /**
     * Accessor. Page edit url
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function editLink(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => route('articles.edit', ['id' => $this->id]),
        );
    }

    /**
     * Scope by rubric
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array                          $id
     */
    public function scopeOfRubric(Builder $query, string|array $id)
    {
        $query->whereHas('rubrics', function($q) use ($id) {
            $q->whereIn('id', (array)$id);
        });
    }
}
