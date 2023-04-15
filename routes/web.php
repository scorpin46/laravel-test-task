<?php

use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('articles.index');
});


Route::group([
    'prefix' => 'articles',
    'as'     => 'articles.',
], function() {
    Route::match(['get', 'post'],'', [ArticleController::class, 'index'])
        ->name('index')
    ;

    Route::get('{id}', [ArticleController::class, 'show'])
        ->name('show')
        ->whereNumber('id')
    ;

    Route::get('{id}/edit', [ArticleController::class, 'edit'])
        ->name('edit')
        ->whereNumber('id')
    ;

    Route::post('{id}/edit', [ArticleController::class, 'update'])
        ->name('update')
        ->whereNumber('id')
    ;

    Route::match(['get', 'post'],'{rubric}', [ArticleController::class, 'rubric'])
        ->name('rubric')
    ;
});