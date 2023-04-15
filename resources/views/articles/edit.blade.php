@extends('_layouts.default')

@section('content')
    <div class="container text-center">
        <h1 class="h2 m-b-20">Редактирование новости с ID: {!! $article->id !!}</h1>
        <p class="h2 m-b-20 {{ Session::get('success') ? '' : 'hidden' }}" style="color: #22ce22" id="success-msg">
            Данные успешно обновлены!
        </p>
        <div class="m-b-20  {{ $errors->any() ? '' : 'hidden' }}" style="color: red" id="errors-msg">
            В форме присутствуют ошибки!
            <br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>

        {{ Form::model($article, ['id' => 'article-edit-form']) }}
            <p class="h3">{{ Form::label('rubrics', 'Рубрики:') }}</p>
            <p>{{ Form::select('rubrics[]', $rubrics->pluck('tree_name', 'id'), $article->rubrics?->pluck('id'), ['required', 'class' => 'form-control full-width', 'multiple', 'size' => 10]) }}</p>

            <p class="h3">{{ Form::label('name', 'Заголовок:') }}</p>
            <p>{{ Form::text('name', null, ['required', 'class' => 'form-control full-width']) }}</p>

            <p class="h3">{{ Form::label('name', 'Анонс:') }}<br><small>(если пусто, подставится автоматически):</small></p>
            <p>{{ Form::textarea('intro_text',  null, ['class' => 'form-control full-width', 'rows' => 5]) }}</p>

            <p class="h3">{{ Form::label('name', 'Детальное описание:') }}</p>
            <p>{{ Form::textarea('text', null, ['required', 'class' => 'form-control full-width', 'rows' => 15]) }}</p>

            <p>
                {{ Form::input('submit', 'save', 'Сохранить и перезагрузить', ['class' => 'form-control btn m-r-20']) }}
                {{ Form::input('submit', 'save-ajax', 'Сохранить асинхронно', ['class' => 'form-control btn m-r-20 save-ajax']) }}
            </p>
        {{ Form::close() }}
    </div>
@endsection

