@extends('blogetc_admin::layouts.admin_layout')
@section('content')
    <div class="alert alert-success">
        <b>Deleted that post</b>
        <br/>
        <a href="{{ route('blogetc.admin.index') }}" class="btn btn-primary">
            &laquo; Back to posts overview
        </a>
    </div>

    @if(count($remainingFeaturedPhotos))
        <p>However, the following featured images were <strong>not</strong> deleted:</p>

        <table class="table">
            <thead>
            <tr>
                <th>Image</th>
                <th>Filename</th>
                <th>Fil esize</th>
                <th>Full location</th>
            </tr>
            </thead>
            <tbody>
            @foreach($remainingFeaturedPhotos as $remainingPhoto)
                <tr>
                    <td>
                        <a href="{{ $remainingPhoto['url'] }}" target="_blank">
                            <img src="{{ $remainingPhoto['url'] }}" width="100" alt="Image preview">
                        </a>
                    </td>
                    <td><code>{{ $remainingPhoto['filename'] }}</code></td>
                    <td>
                        @if(!empty($remainingPhoto['file_size']))
                            {{ $remainingPhoto['file_size'] }}
                        @endif
                    </td>
                    <td>
                        <code>
                            <small>{{ $remainingPhoto['full_path'] }}</small>
                        </code>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <p>
            Please manually remove those files from the filesystem if desired.
        </p>
    @endif

    <hr class="my-5 py-5">

    <p>
        Was deleting it a mistake? Here is some of the output from the deleted post, as JSON. Please use a JSON viewer
        to retrieve the information.
    </p>

    <textarea readonly class="form-control">{{ $deletedPost->toJson() }}</textarea>
@endsection
