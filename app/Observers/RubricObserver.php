<?php

namespace App\Observers;

use App\Models\Rubric as Model;
use Illuminate\Support\Str;

class RubricObserver
{
    public function saving(Model $model): void
    {
        $model->slug = Str::slug($model->name);
    }
}