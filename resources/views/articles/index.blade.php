@extends('_layouts.default')

@section('content')
    <div class="container">
        <div class="menu__search-form text-center">
            <input type="text" id="search-input" placeholder="Поиск"  required="" list="search-suggestions" class="form-control full-width m-t-20 m-b-10">
            <small>
                <div>по умолчанию слова ищутся через OR</div>
                <div>"+" перед словом означает AND</div>
                <div>"-" перед словом означает NOT</div>
                <div>"*" после слова означает поиск продолжения</div>
            </small>
        </div>

        <main class="articles m-t-20" role="main">
            <div class="articles__list" data-lazy-load=".articles__item">
                @include('articles._parts.list')
            </div>
        </main>
    </div>
@endsection