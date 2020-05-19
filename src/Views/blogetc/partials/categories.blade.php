@php
    /** @var \WebDevEtc\BlogEtc\Models\Post $post */
@endphp
<div>
    @foreach($post->categories as $category)
        <a class="btn btn-outline-secondary btn-sm m-1" href="{{ $category->url() }}">
            {{ $category->category_name }}
        </a>
    @endforeach
</div>

