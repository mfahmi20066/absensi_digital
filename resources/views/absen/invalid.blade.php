<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="{{ asset('images/logos/favicon.png?v=3') }}">
        <title>Barcode Tidak Valid - {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 min-h-screen flex items-center justify-center p-6">
        <div class="bg-white rounded-2xl shadow-lg max-w-sm w-full p-8 text-center">
            <img src="{{ asset('images/logos/sppg-logo.png') }}" alt="Logo" class="w-20 h-20 mx-auto rounded-xl object-contain">
            <div class="mt-4 flex items-center justify-center">
                <div class="w-16 h-16 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                    <x-ikon name="x" class="w-8 h-8" />
                </div>
            </div>
            <h1 class="mt-4 text-xl font-bold text-gray-800">Barcode Tidak Valid</h1>
            <p class="mt-2 text-sm text-gray-500">{{ $error ?? session('error', 'Barcode tidak dikenali atau sudah tidak aktif. Hubungi admin SPPG.') }}</p>
            <a href="{{ route('home') }}" class="mt-5 inline-block w-full py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold">Kembali ke Beranda</a>
        </div>
    </body>
</html>
