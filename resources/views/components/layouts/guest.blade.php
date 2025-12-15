@props(['title' => config('app.name')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ trim($title) }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="min-h-dvh bg-zinc-50 text-zinc-900 antialiased">
        <flux:toast />

        <main class="container max-w-6xl mx-auto p-4 md:p-6">
            {{ $slot }}
        </main>
    </body>

</html>
