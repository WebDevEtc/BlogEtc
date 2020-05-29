@extends("layouts.app",['title'=>$title])
@section("content")
    <div class='row'>
        <div class='col-sm-12 blogetc_container'>
            @can(\WebDevEtc\BlogEtc\Gates\GateTypes::MANAGE_ADMIN)
                <div class="text-center">
                    <p class='mb-1'>
                        You are logged in as a blog admin user.
                        <br>
                        <a href='{{route("blogetc.admin.index")}}'
                           class='btn border  btn-outline-primary btn-sm '>
                            <i class="fa fa-cogs" aria-hidden="true"></i>
                            Go To Blog Admin Panel
                        </a>
                    </p>
                </div>
            @endcan

            @if(isset($blogetc_category) && $blogetc_category)
                <h2 class='text-center'>Viewing Category: {{$blogetc_category->category_name}}</h2>
                @if($blogetc_category->category_description)
                    <p class='text-center'>{{$blogetc_category->category_description}}</p>
                @endif
            @endif

            @forelse($posts as $post)
                @include("blogetc::partials.index_loop")
            @empty
                <div class='alert alert-danger'>No posts</div>
            @endforelse

            <div class='text-center  col-sm-4 mx-auto'>
                {{$posts->appends( [] )->links()}}
            </div>
            @include("blogetc::sitewide.search_form")
        </div>
    </div>
@endsection
