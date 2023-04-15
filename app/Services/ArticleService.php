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

    public static function searchByQuery(?string $searchQuery, int $rubricId = null, $paginateQnt = 5)
    {
        $articlesBuilder = Article::query();

        /*
         * Корректировки запроса для inno_db
         * @see https://dev.mysql.com/doc/refman/5.7/en/fulltext-boolean.html
         *
         * Также по FulltextSearch синтаксису в mysql
         * @see http://www.mysql.ru/docs/man/Fulltext_Search.html
         */
        $searchQuery     = preg_replace([
            '#[^\w\d+\- ]#ui', // Убирать все невалидные символы в принципе
            '#([+\- ])+#', // Устранение дубляжа
            '#\b(.+)\b[+\-]+#ui', // Убирать + или - после каждого слова, тк невалидный запрос будет
            '#[^\w\d]+$#ui', // Убирать с конца сроки невалидные символы
            '#[ ]+$#', //Устранение лишних пробелов
            '#^\s*\b(.+)\b\s*$#ui', //Если 1 слово, то добавлять * в конеч
        ], [
            ' ',
            '$1',
            '$1',
            ' ',
            ' ',
            '$1*',
        ], trim($searchQuery));

        if ($rubricId) {
            $articlesBuilder->ofRubric($rubricId);
        }

        $searchQuery = trim($searchQuery);

        if ($searchQuery) {
            //$articlesBuilder->whereFullText(['name', 'text'], $searchQuery, ['mode' => 'boolean']);
            $matchField = "match(name, text) against ('$searchQuery' in boolean mode)";
            $columns = array_merge($articlesBuilder->getModel()->getAttributesKeys(), ['id', "$matchField as score"]);

            $articlesBuilder
                ->selectRaw(implode(', ', $columns ))
                ->whereRaw($matchField)
                ->orderByDesc('score')
            ;
        } else {
            $articlesBuilder->orderByDesc('published_at');
        }

        return $articlesBuilder->paginate($paginateQnt);
    }

}