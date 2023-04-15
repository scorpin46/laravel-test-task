<?php

namespace App\Observers;

use App\Models\Article as Model;
use Illuminate\Support\Str;

class ArticleObserver
{
    public function saving(Model $model): void
    {
        $model->slug       = Str::slug($model->name);
        $model->intro_text = $model->intro_text ?? Str::limit($model->text, 350);
    }
}