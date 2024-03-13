<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Street Group | Please upload a .CSV file of your homeowners</title>
        
        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    </head>
    <body class="antialiased font-bold">
        <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0 dark:text-white">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
                    <div class=" p-8">
                        <h1 class="text-xl py-2">
                            CSV Uploader
                        </h1>

                        <form enctype="multipart/form-data" method="POST" action="{{ route('upload') }}">
                            @csrf
                            <input type="file" name="csv" id="csv" />

                            @if($errors->any())
                                {!! implode('', $errors->all('<p class="font-bold text-red-100 text-sm">:message</p>'))  !!}
                            @endif

                            <input type="submit" class="block p-2 mt-2 border border-white" />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
