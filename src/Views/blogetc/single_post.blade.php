@extends("layouts.app",['title'=>$post->title])
@section("content")



    @include("blogetc::partials.show_errors")
    @include("blogetc::partials.full_post_details")


    @if(config("blogetc.comments.type_of_comments_to_show","built_in") !== 'disabled')
        <div class="container">
            <div>
                <hr>
                <h2 class='text-center' id='blogetccomments'>Comments</h2>
                @include("blogetc::partials.show_comments")
            </div>
        </div>
    @else
        {{--Comments are disabled--}}
    @endif




@endsection