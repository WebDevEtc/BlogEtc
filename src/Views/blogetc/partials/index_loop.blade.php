{{--Used on the index page (so shows a small summary--}}

<div class="card my-5 mx-auto col-md-6 col-sm-10  col-xs-10 ">
    <div class="card-body">
        <?=$post->image_tag("thumbnail", true, 'float-right m-1'); ?>
        <h3 class='card-title'><a href='{{$post->url()}}'>{{$post->title}}</a></h3>
        <h5 class='card-subtitle mb-2 text-muted'>{{$post->subtitle}}</h5>


        <div class='text-center'>
            <a href="{{$post->url()}}" class="card-link btn btn-outline-secondary">View Post</a>
        </div>
    </div>
</div>
