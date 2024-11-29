{{-- resources/views/topics/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Topic</h1>

          {{-- Formatting Toolbar --}}
          <div class="mb-2">
                <!-- Font Size Dropdown with sizes from 1 to 5 -->
                <select id="fontSize" class="form-select form-select-sm d-inline-block" style="width: auto;">
                    <option value="14">1 (Small)</option>
                    <option value="16">2 (Normal)</option>
                    <option value="18">3 (Medium)</option>
                    <option value="20">4 (Large)</option>
                    <option value="22">5 (Extra Large)</option>
                </select>
                <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('size', document.getElementById('fontSize').value)">Font Size</button>

                <!-- Font Color Dropdown -->
                <select id="fontColor" class="form-select form-select-sm d-inline-block" style="width: auto;">
                    <option value="black">Black</option>
                    <option value="red">Red</option>
                    <option value="blue">Blue</option>
                    <option value="green">Green</option>
                    <option value="purple">Purple</option>
                </select>
                <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('color', document.getElementById('fontColor').value)">Font Color</button>

                <!-- Font Family Dropdown -->
                <select id="fontFamily" class="form-select form-select-sm d-inline-block" style="width: auto;">
                    <option value="Arial">Arial</option>
                    <option value="Verdana">Verdana</option>
                    <option value="Courier">Courier</option>
                    <option value="Georgia">Georgia</option>
                    <option value="Times New Roman">Times New Roman</option>
                </select>
                <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('font', document.getElementById('fontFamily').value)">Font Family</button>

                <!-- Other Buttons -->
                <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('youtube')">YouTube</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('center')">Center</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('quote')">Quote</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="insertBBCode('img')">Image</button>

            </div>

    <form action="{{ route('forum.update', $topic->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $topic->title) }}" required>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea name="content" id="content" class="form-control" rows="5" required>{{ old('content', $topic->content) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Topic</button>
        <a href="{{ route('forum.show', $topic->id) }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
function insertBBCode(tag, option = null) {
    const textarea = document.getElementById("content");
    const startTag = option ? `[${tag}=${option}]` : `[${tag}]`;
    const endTag = `[/${tag}]`;
    const cursorPosition = textarea.selectionStart; // Store current cursor position
    const selectedText = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);

    // Insert the BBCode tags with the selected text in the middle
    const newText = startTag + selectedText + endTag;
    textarea.value = textarea.value.substring(0, cursorPosition) + newText + textarea.value.substring(textarea.selectionEnd);

    // Set the cursor position in the middle of the tags, right after the opening tag
    const newCursorPosition = cursorPosition + startTag.length;
    textarea.selectionStart = newCursorPosition;
    textarea.selectionEnd = newCursorPosition;

    // Focus back on the textarea
    textarea.focus();
}
</script>
@endsection
