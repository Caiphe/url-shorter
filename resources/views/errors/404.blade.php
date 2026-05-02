<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>{{ __('Page not found') }} — {{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any" />
        <link rel="icon" href="/favicon.svg" type="image/svg+xml" />

        @vite(['resources/css/app.css'])
    </head>
    <body class="flex min-h-screen flex-col items-center justify-center bg-white p-6 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <main class="max-w-md text-center">
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">404</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight">{{ __('Page not found') }}</h1>
            <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('The page you are looking for does not exist or the link is invalid.') }}
            </p>
            <p class="mt-8">
                <a
                    href="{{ route('login') }}"
                    class="text-sm font-medium text-zinc-900 underline decoration-zinc-400 underline-offset-4 hover:decoration-zinc-900 dark:text-white dark:hover:decoration-white"
                >
                    {{ __('Go to sign in') }}
                </a>
            </p>
        </main>
    </body>
</html>
