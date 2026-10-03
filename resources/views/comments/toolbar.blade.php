<div class="discussion-toolbar" aria-label="Text formatting">
    @foreach(['b' => ['Bold', 'type-bold'], 'i' => ['Italic', 'type-italic'], 'quote' => ['Quote', 'quote'], 'spoiler' => ['Spoiler', 'eye-slash']] as $tag => [$label, $icon])
        <button type="button" data-comment-tag="{{ $tag }}" aria-label="{{ $label }}" title="{{ $label }}"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></button>
    @endforeach
</div>
