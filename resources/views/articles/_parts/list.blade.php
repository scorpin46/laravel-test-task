@foreach($articles as $article)
    @php /** @var App\Models\Article $article */ @endphp

    <article class="articles__item" data-id="{{ $article->id }}">
        <header>
            <div class="articles__item-header">
                <span class="article-date" title="Дата публикации">{{ $article->published_at->format('d.m.Y') }}</span>
                <div class="article-rubric">
                    <span>{!! $article->rubrics()->get()->implode('tree_name_links', '&ensp; | &ensp;') !!}</span>
                    <a class="m-l-30 article-edit" title="Редактировать" href="{{ $article->edit_link }}" target="_blank"></a>
                </div>
            </div>
            <h1 class="h1 articles__item-title">
                {!! $article->name !!}
            </h1>
        </header>
        <div class=" p-l-30 p-r-30  p-b-20">
            <div class="articles__item-text">
                <div id="{{ $article->id }}-intro-text">
                    {!! $article->intro_text !!}
                </div>
            </div>
            <div id="article-{{ $article->id }}" class="collapse-block collapsed">
                {!! $article->text !!}
            </div>
        </div>

        @if (trim($article->text))
            <div class="text-center">
                <a href="{{ $article->link }}" class="btn btn--sm articles__item-more" data-collapse="true"
                   data-target="#article-{{ $article->id }}" data-toggle-title="Свернуть" onclick="$('#{{ $article->id }}-intro-text').toggle()">
                    <span class="toggle-title">Подробнее</span>
                </a>
            </div>
        @endif
    </article>
@endforeach
@if( ! $articles->count() )
    Данных не найдено...
@endempty