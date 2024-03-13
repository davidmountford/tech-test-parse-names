<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Street Group - Thanks for uploading! Here's your results.</title>

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    </head>
    <body class="antialiased">
        <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0 dark:text-white">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
                    <div class="preformatted p-4">
                        @foreach($output ?? '' as $set)
                            <pre>{{ var_export($set ?? '') }}</pre>
                        @endforeach
                    </div>
                </div>
                <div class="mt-2 text-sM">
                    <a href="{{ route('upload')}}">< back</a>
                </div>
            </div>
        </div>
    </body>
</html>
