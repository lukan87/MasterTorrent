<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Debug</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container text-center">
        <h1>Error Debug Page</h1>
        <pre>{{ $exception->getMessage() }}</pre>
        <pre>{{ $exception->getTraceAsString() }}</pre>
    </div>
</body>
</html>
