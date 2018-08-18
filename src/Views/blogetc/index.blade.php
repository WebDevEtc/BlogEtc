@extends("layouts.app",['title'=>$title])
@section("content")

    @if(isset($blogetc_category) && $blogetc_category)

        <h2 class='text-center'>Viewing Category: {{$blogetc_category->category_name}}</h2>

        @endif

    @forelse($posts as $post)
        @include("blogetc::partials.index_loop")
    @empty
        <div class='alert alert-danger'>No posts</div>
    @endforelse

    <div class='text-center  col-sm-4 mx-auto'>
        {{$posts->appends( [] )->links()}}
    </div>

@endsection