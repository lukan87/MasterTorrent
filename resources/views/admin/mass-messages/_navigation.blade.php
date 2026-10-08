<nav class="mm-tabs mb-4" aria-label="User management">
    <a href="{{ route('admin.users.index') }}" @if(request()->routeIs('admin.users.index', 'admin.users.show', 'admin.users.edit')) aria-current="page" @endif><i class="bi bi-people"></i> Users</a>
    <a href="{{ route('admin.users.mass-messages.index') }}" @if(request()->routeIs('admin.users.mass-messages.*')) aria-current="page" @endif><i class="bi bi-broadcast"></i> Mass Messages</a>
    <a href="{{ route('admin.users.comments') }}"><i class="bi bi-chat-square-text"></i> Comments</a>
    <a href="{{ route('uploadapps.index') }}"><i class="bi bi-cloud-upload"></i> Upload Apps</a>
    <a href="{{ route('warnings.index') }}"><i class="bi bi-exclamation-triangle"></i> Hit &amp; Runs</a>
    @if(auth()->user()?->user_class >= \App\Models\UserClass::ADMIN)
        <a href="{{ route('admin.messages.index') }}"><i class="bi bi-envelope"></i> User Messages</a>
    @endif
</nav>
