<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coder Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
    min-height: 100vh;
    display: flex;
    flex-direction: row;
    background-color: #12121b; /* very dark background for the page */
    color: #ffffff; /* default text color white */
}
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
        }
        .sidebar a:hover {
            background-color: #495057;
            color: #fff;
        }
       .content {
    flex-grow: 1;
    padding: 20px;
    background-color: #12121b; /* match the page background */
}
.card {
    background-color: #1e1e2f; /* all cards dark by default */
    color: #ffffff;
}
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h3 class="p-3">Coder Admin</h3>
        <a href="{{ route('coder.index') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="#"><i class="bi bi-people"></i> Users</a>
        <a href="#"><i class="bi bi-hdd-stack"></i> Torrents</a>
        <a href="#"><i class="bi bi-gear"></i> Settings</a>
        <a href="/"><i class="bi bi-backspace-fill"></i> Go back to site</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        {{-- <nav class="navbar bg-body-tertiary">
  <div class="container">
    <a class="navbar-brand" href="#">
      <img src="/docs/5.3/assets/brand/bootstrap-logo.svg" alt="Bootstrap" width="30" height="24">
    </a>
  </div>
</nav> --}}

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('scripts')
</body>
</html>
