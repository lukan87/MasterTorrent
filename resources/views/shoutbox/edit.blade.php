@extends('layouts.app')

@section('content')

    <div class="row justify-content-center mt-5">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header text-center">
                    <h4>Edit Message</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('shoutbox.update', $messages->id) }}" method="post">
                        @csrf
                        @method('put')
                        <div class="form-group">
                            <label for="content">Message:</label>
                            <textarea class="form-control" name="content" id="content" rows="4" required>{{ $messages->message }}</textarea>
                        </div>
                        <div class="text-center mt-3">
                            <button class="btn btn-success btn-sm" type="submit">Edit Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
