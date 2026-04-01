<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error {{ $code }}</title>
    <style>
        body { font-family: system-ui, sans-serif; line-height: 1.5; margin: 0; padding: 2rem; background: #f7fafc; color: #1a202c; }
        .container { max-width: 48rem; margin: 0 auto; }
        h1 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        .code { font-weight: 600; color: #e53e3e; }
        pre { background: #2d3748; color: #e2e8f0; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1><span class="code">{{ $code }}</span> {{ $message }}</h1>
        <p>{{ get_class($exception) }}</p>
        @if($trace)
            <h2>Stack trace</h2>
            <pre>{{ $trace }}</pre>
        @endif
    </div>
</body>
</html>
