@extends('_layouts.default')

@section('content')
    <div class="container">
        <div class="article">
            <div class="article__header">
                <span class="article-date" title="Дата публикации">{{ $article->published_at->format('d.m.Y') }}</span>
                <div class="article-rubric">
                    <span>{!! $article->rubrics()->get()->implode('tree_name_links', '&ensp; | &ensp;') !!}</span>
                    <a class="m-l-30 article-edit" title="Редактировать" href="{{ $article->edit_link }}" target="_blank"></a>
                </div>
            </div>
            <div class="article__text p-l-30 p-r-30 p-b-20">
                {!! $article->text !!}
            </div>
        </div>
    </div>
@endsection

