
@if (isset($errors) && count($errors))
    <div class="alert alert-danger col-md-6 mx-auto">
                    <strong>Sorry, but there was an error:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
@endif
