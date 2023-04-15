<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @mixin QueryBuilder
 */
class BaseEloquentBuilder extends EloquentBuilder
{
    /**
     * BaseEloquentBuilder constructor.
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function __construct(QueryBuilder $query)
    {
        parent::__construct($query);
    }

    /**
     * Find a model by a given key
     *
     * @param string $columnName
     * @param        $value
     * @param array  $columns
     *
     * @return BaseEloquentBuilder|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function findBy(string $columnName, $value, array $columns = ['*'])
    {
        $this->query->where($columnName, '=', $value);

        return $this->first($columns);
    }

    /**
     * Find a model by a given array key
     *
     * @param string $columnName
     * @param        $value
     * @param array  $columns
     *
     * @return \Illuminate\Support\Collection
     */
    public function findManyBy(string $columnName, $value, array $columns = ['*'])
    {
        $this->query->whereIn($columnName, (array)$value);

        return $this->get($columns);
    }

    /**
     * @param \Carbon\Carbon $fromDateTime
     * @param \Carbon\Carbon $toDatetime
     *
     * @return BaseEloquentBuilder
     */
    public function withCreatedAtBetween(Carbon $fromDateTime, Carbon $toDatetime): BaseEloquentBuilder
    {
        return $this
            ->where('created_at', '>=', $fromDateTime)
            ->where('created_at', '<=', $toDatetime);
    }
}
