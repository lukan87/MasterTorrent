<!DOCTYPE html>
<html lang="en">
<head>
    <script src="{{ asset('js/theme.js') }}?v={{ filemtime(public_path('js/theme.js')) }}"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Debug Error | FileIPlay</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet"
          href="{{ asset('css/dark-theme.css') }}?v={{ filemtime(public_path('css/dark-theme.css')) }}">
    <link rel="stylesheet"
          href="{{ asset('css/theme.css') }}?v={{ filemtime(public_path('css/theme.css')) }}">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: monospace;
        }

        .debug-wrapper {
            width: 95%;
            max-width: 1500px;
            margin: 30px auto;
        }

        .debug-header {
            padding: 24px;
            margin-bottom: 20px;
            border: 1px solid rgba(220, 53, 69, .4);
            border-left: 5px solid #dc3545;
            border-radius: 8px;
        }

        .debug-header h1 {
            margin: 0 0 10px;
            font-size: 26px;
        }

        .debug-message {
            font-size: 16px;
            line-height: 1.6;
            word-break: break-word;
            white-space: pre-wrap;
        }

        .debug-card {
            margin-bottom: 20px;
            border: 1px solid rgba(128, 128, 128, .25);
            border-radius: 8px;
            overflow: hidden;
        }

        .debug-card-header {
            padding: 12px 16px;
            font-weight: 700;
            font-size: var(--site-font-body, 13px);
            border-bottom: 1px solid rgba(128, 128, 128, .25);
        }

        .debug-card-body {
            padding: 16px;
        }

        .debug-info-grid {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 10px;
        }

        .debug-label {
            font-weight: 700;
        }

        .debug-value {
            word-break: break-all;
        }

        .code-container {
            overflow-x: auto;
            font-family: Consolas, Monaco, "Courier New", monospace;
            font-size: var(--site-font-body, 13px);
            line-height: 1.6;
        }

        .code-line {
            display: flex;
            min-width: max-content;
            padding: 1px 10px;
        }

        .code-line.error-line {
            background: rgba(220, 53, 69, .20);
            border-left: 4px solid #dc3545;
        }

        .line-number {
            width: 70px;
            min-width: 70px;
            padding-right: 15px;
            text-align: right;
            user-select: none;
            opacity: .6;
        }

        .line-code {
            white-space: pre;
        }

        .trace {
            margin: 0;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-word;
            font-family: Consolas, Monaco, "Courier New", monospace;
            font-size: var(--site-font-body, 13px);
            line-height: 1.6;
        }

        .debug-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .debug-btn {
            display: inline-block;
            padding: 9px 15px;
            border: 1px solid rgba(128, 128, 128, .35);
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            font: inherit;
        }

        @media (max-width: 700px) {
            .debug-info-grid {
                grid-template-columns: 1fr;
            }

            .debug-wrapper {
                width: 96%;
                margin: 15px auto;
            }
        }
    </style>
</head>

<body>

@php
    /*
    |--------------------------------------------------------------------------
    | Exception information
    |--------------------------------------------------------------------------
    */

    $exceptionClass = get_class($exception);
    $message = $exception->getMessage();
    $file = $exception->getFile();
    $line = $exception->getLine();
    $code = $exception->getCode();

    /*
    |--------------------------------------------------------------------------
    | HTTP status
    |--------------------------------------------------------------------------
    */

    $statusCode = method_exists($exception, 'getStatusCode')
        ? $exception->getStatusCode()
        : 500;

    /*
    |--------------------------------------------------------------------------
    | Read source code around failing line
    |--------------------------------------------------------------------------
    */

    $sourceLines = [];
    $startLine = 0;
    $endLine = 0;

    if (is_file($file) && is_readable($file)) {
        $allLines = @file($file);

        if ($allLines !== false) {
            $startLine = max(1, $line - 12);
            $endLine = min(count($allLines), $line + 12);

            for ($i = $startLine; $i <= $endLine; $i++) {
                $sourceLines[$i] = $allLines[$i - 1] ?? '';
            }
        }
    }
@endphp

<div class="debug-wrapper">

    {{-- ========================================================= --}}
    {{-- ERROR HEADER --}}
    {{-- ========================================================= --}}

    <div class="debug-header">

        <h1>
            {{ class_basename($exception) }}
        </h1>

        <div class="debug-message">
            {{ $message ?: 'No exception message was provided.' }}
        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- ACTIONS --}}
    {{-- ========================================================= --}}

    <div class="debug-actions">

        <button
            type="button"
            class="debug-btn"
            onclick="window.location.reload()"
        >
            Reload Page
        </button>

        <button
            type="button"
            class="debug-btn"
            onclick="history.back()"
        >
            Go Back
        </button>

        <a href="{{ url('/') }}" class="debug-btn">
            Home
        </a>

    </div>


    {{-- ========================================================= --}}
    {{-- EXCEPTION DETAILS --}}
    {{-- ========================================================= --}}

    <div class="debug-card">

        <div class="debug-card-header">
            Exception Details
        </div>

        <div class="debug-card-body">

            <div class="debug-info-grid">

                <div class="debug-label">Exception</div>
                <div class="debug-value">
                    {{ $exceptionClass }}
                </div>

                <div class="debug-label">HTTP Status</div>
                <div class="debug-value">
                    {{ $statusCode }}
                </div>

                <div class="debug-label">Exception Code</div>
                <div class="debug-value">
                    {{ $code ?: 'N/A' }}
                </div>

                <div class="debug-label">File</div>
                <div class="debug-value">
                    {{ $file }}
                </div>

                <div class="debug-label">Line</div>
                <div class="debug-value">
                    {{ $line }}
                </div>

                <div class="debug-label">Request</div>
                <div class="debug-value">
                    {{ request()->method() }} {{ request()->fullUrl() }}
                </div>

                <div class="debug-label">Route</div>
                <div class="debug-value">
                    {{ request()->route()?->getName() ?? 'N/A' }}
                </div>

                <div class="debug-label">User ID</div>
                <div class="debug-value">
                    {{ auth()->id() ?? 'Guest' }}
                </div>

                <div class="debug-label">PHP</div>
                <div class="debug-value">
                    {{ PHP_VERSION }}
                </div>

                <div class="debug-label">Laravel</div>
                <div class="debug-value">
                    {{ app()->version() }}
                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- SOURCE CODE --}}
    {{-- ========================================================= --}}

    <div class="debug-card">

        <div class="debug-card-header">
            Source Code — Line {{ $line }}
        </div>

        <div class="debug-card-body" style="padding: 0;">

            @if(count($sourceLines))

                <div class="code-container">

                    @foreach($sourceLines as $lineNumber => $sourceLine)

                        <div class="code-line {{ $lineNumber === $line ? 'error-line' : '' }}">

                            <span class="line-number">
                                {{ $lineNumber }}
                            </span>

                            <span class="line-code">{{ rtrim($sourceLine, "\r\n") }}</span>

                        </div>

                    @endforeach

                </div>

            @else

                <div style="padding: 16px;">
                    Source code could not be read from this file.
                </div>

            @endif

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- STACK TRACE --}}
    {{-- ========================================================= --}}

    <div class="debug-card">

        <div class="debug-card-header">
            Stack Trace
        </div>

        <div class="debug-card-body">

            <pre class="trace">{{ $exception->getTraceAsString() }}</pre>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- PREVIOUS EXCEPTION --}}
    {{-- ========================================================= --}}

    @if($exception->getPrevious())

        <div class="debug-card">

            <div class="debug-card-header">
                Previous Exception
            </div>

            <div class="debug-card-body">

                <div class="debug-info-grid">

                    <div class="debug-label">Exception</div>
                    <div class="debug-value">
                        {{ get_class($exception->getPrevious()) }}
                    </div>

                    <div class="debug-label">Message</div>
                    <div class="debug-value">
                        {{ $exception->getPrevious()->getMessage() }}
                    </div>

                    <div class="debug-label">File</div>
                    <div class="debug-value">
                        {{ $exception->getPrevious()->getFile() }}
                    </div>

                    <div class="debug-label">Line</div>
                    <div class="debug-value">
                        {{ $exception->getPrevious()->getLine() }}
                    </div>

                </div>

            </div>

        </div>

    @endif

</div>

</body>
</html>
