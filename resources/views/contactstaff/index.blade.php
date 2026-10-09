@extends('layouts.app')
@section('title', 'Contact Staff Requests')
@section('content')
@vite('resources/js/contact-browser.js')
<div class="contact-support contact-support--staff" data-contact-app data-draft-user="{{ auth()->id() ?? 'guest' }}" data-contact-list>
    @include('contact.partials.hero', [
        'icon' => 'bi-person-lines-fill',
        'eyebrow' => 'Staff workspace',
        'heading' => 'Contact requests',
        'description' => 'Help members and guests get back on track. This inbox updates automatically while open.',
    ])
    <section class="contact-panel" data-contact-list-content>@include('contactstaff.partials.list')</section>
</div>
@endsection
