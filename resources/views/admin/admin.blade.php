<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>@yield('title','Admin')</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
background:#f5f7fb;
}

.admin-sidebar{
width:260px;
height:100vh;
position:fixed;
left:0;
top:0;
background:#111827;
color:white;
}

.admin-sidebar a{
display:block;
padding:12px 20px;
color:#cbd5e1;
text-decoration:none;
font-size:14px;
}

.admin-sidebar a:hover{
background:#1f2937;
color:white;
}

.admin-content{
margin-left:260px;
}

.admin-header{
background:white;
border-bottom:1px solid #e5e7eb;
padding:15px 25px;
}
</style>

</head>

<body>

<div class="admin-sidebar">

<div class="p-4 border-bottom">
<h5 class="mb-0">Admin Panel</h5>
</div>

<a href="{{ route('admin.index') }}">
<i class="bi bi-speedometer2 me-2"></i> Dashboard
</a>

<a href="{{ route('admin.torrents.index') }}">
<i class="bi bi-download me-2"></i> Torrents
</a>

<a href="{{ route('admin.users.index') }}">
<i class="bi bi-people me-2"></i> Users
</a>

<a href="{{ route('admin.movies.index') }}">
<i class="bi bi-film me-2"></i> Movies
</a>

<a href="{{ route('admin.series.index') }}">
<i class="bi bi-collection-play me-2"></i> Series
</a>

<a href="{{ route('happyhour.index') }}">
<i class="bi bi-clock-history me-2"></i> Happy Hour
</a>

@if(Auth::check() && Auth::user()->user_class === \App\Models\UserClass::WEB_DEVELOPER)
<a href="{{ route('admin.systemInfo.index') }}">
<i class="bi bi-terminal me-2"></i> System Info
</a>
@endif

</div>

<div class="admin-content">

<div class="admin-header d-flex justify-content-between">

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