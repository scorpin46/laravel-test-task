<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Rubric;
use App\Services\ArticleService;
use Arr;
use Illuminate\Http\Request;
use Response;

class ArticleController extends Controller
{
    /**
     * Articles list page
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index(int $rubricId = null)
    {
        $articlesBuilder = Article::query();
        $searchQuery     = request('searchQuery');

        if ($rubricId) {
            $articlesBuilder->ofRubric($rubricId);
        }

        if (trim($searchQuery)) {
            $articlesBuilder->whereFullText(['name', 'text'], $searchQuery);
        } else {
            $articlesBuilder->orderByDesc('published_at');
        }

        $articles = $articlesBuilder->paginate(5);
        $view     = request()->ajax()
            ? 'articles._parts.list'
            : 'articles.index';

        return view($view, [
            'articles' => $articles,
        ]);
    }

    public function rubric(string $rubric)
    {
        $rubric = Rubric::query()->where('slug', $rubric)->firstOrFail();

        return $this->index($rubric->id);
    }


    /**
     * @param int $id
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function show(int $id)
    {
        $article = Article::findOrFail($id);

        return view('articles.show', [
            'article' => $article,
        ]);
    }


    /**
     * @param int $id
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(int $id)
    {
        $article = Article::findOrFail($id);
        $rubrics = Rubric::get()->sortBy('tree_name');

        return view('articles.edit', [
            'article' => $article,
            'rubrics' => $rubrics,
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        $article = Article::findOrFail($id);
        ArticleService::updateArticle($article, $request->all());

        if ($request->ajax()) {
            return Response::json([
                'success' => $article->isValid(),
                'errors'  => Arr::flatten($article->getErrors()->toArray()),
            ]);
        }

        return $article->isInvalid()
            ? redirect()->back()
                ->withErrors($article->getErrors())
                ->withInput()
            : redirect()->back()->with('success', true);
    }
}
