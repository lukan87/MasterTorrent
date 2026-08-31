@extends('layouts.app')

@section('content')

<div class="container mt-5">

<div class="card glass border-0 shadow">

<div class="card-header">

<h4>Create Support Ticket</h4>

</div>

<div class="card-body">

<form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">

@csrf


<div class="mb-3">

<label class="form-label">Category</label>

<select name="category_id" class="form-select" required>

@foreach($categories as $category)

<option value="{{ $category->id }}">

{{ $category->name }}

</option>

@endforeach

</select>

</div>


<div class="mb-3">

<label class="form-label">Priority</label>

<select name="priority" class="form-select">

<option value="Low">Low</option>
<option value="Medium">Medium</option>
<option value="High">High</option>
<option value="Critical">Critical</option>

</select>

</div>


<div class="mb-3">

<label class="form-label">Title</label>

<input type="text" name="title" class="form-control" required>

</div>


@if($torrent)

<div class="mb-3">

<label class="form-label">Reporting Torrent</label>

<input type="text"
class="form-control"
value="{{ $torrent->name }}"
readonly>

<input type="hidden"
name="linked_torrent_id"
value="{{ $torrent->id }}">

<input type="hidden"
name="category_id"
value="4">

</div>

@endif

<div class="mb-3">

<label class="form-label">Description</label>

<textarea name="description" rows="6" class="form-control" required></textarea>

</div>

<div class="mb-3">

<label class="form-label">Attachment</label>

<div id="drop-area"
class="border border-secondary rounded p-4 text-center"
style="cursor:pointer;">

<p class="mb-2">Drag & drop a file here</p>

<p class="text-muted small">or click to select a file</p>

<input type="file"
name="attachment[]"
id="attachment"
multiple
class="d-none">

<span id="file-name" class="text-info"></span>

</div>


<small class="text-muted">
Optional (screenshots, logs, etc.)
</small>

</div>


<button class="btn btn-primary">

Submit Ticket

</button>

</form>

</div>

</div>

</div>

<style>

#drop-area:hover{
    background:#565555;
}

</style>

<script>

const dropArea = document.getElementById('drop-area');
const fileInput = document.getElementById('attachment');
const fileName = document.getElementById('file-name');

dropArea.addEventListener('click', () => {
    fileInput.click();
});

fileInput.addEventListener('change', () => {
   if(fileInput.files.length){

    let names = [];

    for(let i=0;i<fileInput.files.length;i++){
        names.push(fileInput.files[i].name);
    }

    fileName.textContent = names.join(', ');
}
});

dropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropArea.classList.add('bg-dark');
});

dropArea.addEventListener('dragleave', () => {
    dropArea.classList.remove('bg-dark');
});

dropArea.addEventListener('drop', (e) => {

    e.preventDefault();

    dropArea.classList.remove('bg-dark');

    if(e.dataTransfer.files.length){

fileInput.files = e.dataTransfer.files;

let names = [];

for(let i=0;i<e.dataTransfer.files.length;i++){
    names.push(e.dataTransfer.files[i].name);
}

fileName.textContent = names.join(', ');

    }

});

</script>

@endsection