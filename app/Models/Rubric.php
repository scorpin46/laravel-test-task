<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rubric extends BaseModel
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected array $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'slug'      => null,
        'name'      => null,
        'parent_id' => null,
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'slug'      => 'string',
        'name'      => 'string',
        'parent_id' => 'integer',
    ];

    /**
     * @var string[]
     */
    protected array $rules = [
        'slug'      => 'string|max:255|unique:rubrics',
        'name'      => 'string|max:255',
        'parent_id' => 'integer',
    ];

    /**
     * {@inheritDoc}
     */
    protected $scenarios = [
        self::SCENARIO_DEFAULT => [
            '!slug',
            '!name',
            '!parent_id',
        ],
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'rubric_article');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    /**
     * Accessor. Page url
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function link(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => route('articles.rubric', ['rubric' => $this->slug]),
        );
    }

    /**
     * @param false $asTag
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function treeName(bool $asTag = false): Attribute
    {
        $treeName = $asTag
            ? $this?->parent?->tree_name_links
            : $this?->parent?->tree_name
        ;

        $name = $asTag
            ? "<a href=\"$this->link\">$this->name</a>"
            : $this->name;

        return Attribute::make(
            get: fn(?self $value) => $treeName
                ? $treeName . ' > ' . $name
                : $name
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function treeNameLinks(): Attribute
    {
        return $this->treeName(true);
    }

}
