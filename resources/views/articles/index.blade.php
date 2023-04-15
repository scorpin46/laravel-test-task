@extends('_layouts.default')

@section('content')
    <div class="container">
        <div class="menu__search-form">
            <input type="text" id="search-input" placeholder="Поиск"  required="" list="search-suggestions" class="form-control full-width m-b-20 m-t-20">
            <datalist id="search-suggestions"></datalist>
        </div>

        <main class="articles" role="main">
            <div class="articles__list" data-lazy-load=".articles__item">
                @include('articles._parts.list')
            </div>
        </main>
    </div>
@endsection