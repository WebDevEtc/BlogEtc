@extends('layouts.app',['title' => $title])
@section('content')

    {{--https://webdevetc.com/laravel/packages/blogetc-blog-system-for-your-laravel-app/help-documentation/laravel-blog-package-blogetc#guide_to_views--}}

    <div class="row">
        <div class="col-sm-12">
            <h2>Search Results for {{ $query }}</h2>

            @forelse($search_results as $result)
                <h2>Search result #{{ $loop->iteration }}</h2>
                @include('blogetc::partials.index_loop', ['post' => $result->indexable])
            @empty
                <div class="alert alert-danger">Sorry, but there were no results!</div>
            @endforelse

            @include('blogetc::sitewide.search_form')
        </div>
    </div>
@endsection

