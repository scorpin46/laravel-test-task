<?php

namespace App\Services;

use App\Models\Article;
use Validator;

class ArticleService
{
    public static function updateArticle(Article $article, array $attrs)
    {
        $isValid = $article->setScenario($article::SCENARIO_UPDATE)
            ->fill($attrs)
            ->isValid();

        $article->__unset('rubrics');

        if ($isValid && $article->save() && isset($attrs['rubrics'])){
            $article->rubrics()->sync($attrs['rubrics']);
        }

        return $article;
    }

}