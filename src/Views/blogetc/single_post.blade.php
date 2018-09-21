@extends("layouts.app",['title'=>$post->title])
@section("content")


    <div class='container'>
    <div class='row'>
        <div class='col-sm-12 col-md-12 col-lg-12'>

            @include("blogetc::partials.show_errors")
            @include("blogetc::partials.full_post_details")


            @if(config("blogetc.comments.type_of_comments_to_show","built_in") !== 'disabled')
                <div class="" id='maincommentscontainer'>
                    <h2 class='text-center' id='blogetccomments'>Comments</h2>
                    @include("blogetc::partials.show_comments")
                </div>
            @else
                {{--Comments are disabled--}}
            @endif


        </div>
    </div>
    </div>

@endsection