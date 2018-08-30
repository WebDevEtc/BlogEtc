<hr>
<h5 class='text-center'>Add a comment</h5>
<form method='post' action='{{route("blogetc.comments.add_new_comment", $post->slug)}}'>
    @csrf



    @if(config("blogetc.comments.save_user_id_if_logged_in", true) == false || !\Auth::check())
        <div class="form-group mx-auto col-md-6 ">
            <label id="author_name_label" for="author_name">Your Name </label>
            <input
                    type='text'
                    class="form-control"
                    name='author_name'
                    id="author_name"
                    placeholder="Your name"
                    required
                   value="{{old("author_name")}}">
        </div>
    @endif


    <div class="form-group col-md-6 mx-auto">

            <label id="comment_label" for="comment">Your Comment </label>
                    <textarea
                            class="form-control"
                            name='comment'
                            required
                            id="comment"
                            placeholder="Write your comment here"
                            rows="7">{{old("comment")}}</textarea>

    </div>

    @if($captcha)
        {{--Captcha is enabled. Load the type class, and then include the view as defined in the captcha class --}}
        @include($captcha->view())
    @endif


    <div class="form-group col-md-2 mx-auto">
        <input type='submit' class="form-control input-sm btn btn-success "
               value='Add Comment'>
    </div>

</form>
