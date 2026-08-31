<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>@yield('title','Admin')</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#0f172a;
color:#e2e8f0;
font-family:system-ui;
}

/* Sidebar */

.admin-sidebar{
width:260px;
height:100vh;
position:fixed;
left:0;
top:0;
background:#020617;
border-right:1px solid #1e293b;
}

.admin-sidebar h5{
color:#f1f5f9;
}

.admin-sidebar a{
display:block;
padding:12px 20px;
color:#94a3b8;
text-decoration:none;
font-size:14px;
transition:all .2s;
}

.admin-sidebar a:hover{
background:#1e293b;
color:#ffffff;
}

/* Content */

.admin-content{
margin-left:260px;
}

/* Header */

.admin-header{
background:#020617;
border-bottom:1px solid #1e293b;
padding:15px 25px;
}

/* Cards */

.card{
background:#020617;
border:1px solid #1e293b;
color:#e2e8f0;
}

/* Links */

a{
color:#38bdf8;
}

/* Badge */

.badge{
font-weight:500;
}

.admin-sidebar a.active{
background:#2563eb;
color:white;
}

</style>

</head>

<body>

<div class="admin-sidebar">

<div class="p-4 border-bottom border-secondary">
<h5 class="mb-0">Admin Panel</h5>
</div>


<a href="{{ route('admin.index') }}" class="{{ request()->routeIs('admin.index') ? 'active' : '' }}">Dashboard</a>

<a href="{{ route('admin.torrents.index') }}" class="{{ request()->routeIs('admin.torrents.index') ? 'active' : '' }}">
<i class="bi bi-download me-2"></i> Torrents
</a>

<a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
<i class="bi bi-people me-2"></i> Users
</a>

<a href="{{ route('admin.movies.index') }}" class="{{ request()->routeIs('admin.movies.index') ? 'active' : '' }}">
<i class="bi bi-film me-2"></i> Movies
</a>

<a href="{{ route('admin.series.index') }}" class="{{ request()->routeIs('admin.series.index') ? 'active' : '' }}">
<i class="bi bi-collection-play me-2"></i> Series
</a>

<a href="{{ route('happyhour.index') }}" class="{{ request()->routeIs('happyhour.index') ? 'active' : '' }}">
<i class="bi bi-clock-history me-2"></i> Happy Hour
</a>

@if(Auth::check() && Auth::user()->user_class === \App\Models\UserClass::WEB_DEVELOPER)
<a href="{{ route('admin.systemInfo.index') }}" class="{{ request()->routeIs('admin.systemInfo.index') ? 'active' : '' }}">
<i class="bi bi-terminal me-2"></i> System Info
</a>
@endif

<a href="/">
<i class="bi bi-backspace-fill me-2"></i> Go back to site
</a>

</div>


<div class="admin-content">

<div class="admin-header d-flex justify-content-between align-items-center">

<h5 class="mb-0">@yield('title')</h5>

<div>
<span class="badge bg-danger">Admin</span>
</div>

</div>

<div class="p-4">

@yield('content')

</div>

</div>

</body>
</html>