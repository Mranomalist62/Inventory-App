<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Sistem POS & Inventory') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Figtree', 'sans-serif'],
                    },
                    colors: {
                        primary: '#d97706', // Amber 600 (Filament Default vibe)
                        secondary: '#111827', // Gray 900
                    }
                }
            }
        }
    </script>
</head>

<body class="antialiased bg-gray-50 text-gray-800">

    <nav class="bg-white shadow-sm fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <a href="#" class="flex items-center gap-2">
                        <img src="{{ asset('images/logo.png') }}" alt="InvenPOS Logo" class="h-8 w-8">
                        <span class="font-bold text-xl tracking-tight text-gray-900">InvenPOS</span>
                    </a>
                </div>
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="#fitur" class="text-gray-600 hover:text-primary transition">Fitur</a>

                    {{-- Cek apakah user sedang login (Filament Auth) --}}
                    @if (auth()->check())
                        <a href="{{ route('filament.admin.pages.dashboard') }}"
                            class="font-semibold text-primary hover:text-amber-700">
                            Ke Dashboard
                        </a>
                    @else
                        {{-- Tombol Login Filament --}}
                        <a href="{{ route('filament.admin.auth.login') }}"
                            class="text-gray-700 hover:text-primary font-semibold transition">
                            Masuk
                        </a>

                        {{-- Cek apakah route register filament ada (jika diaktifkan di Provider) --}}
                        @if (Route::has('filament.admin.auth.register'))
                            <a href="{{ route('filament.admin.auth.register') }}"
                                class="bg-primary hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition shadow-lg shadow-amber-500/30">
                                Daftar
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <section class="pt-32 pb-20 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-6xl font-extrabold text-gray-900 mb-6 leading-tight">
                Kelola Toko & Stok <br>
                <span
                    class="text-primary bg-clip-text text-transparent bg-gradient-to-r from-amber-500 to-orange-600">Tanpa
                    Ribet</span>
            </h1>
            <p class="text-lg md:text-xl text-gray-500 mb-10 max-w-3xl mx-auto">
                Sistem Inventory dan Kasir modern berbasis Filament. Pantau performa bisnis Anda dari mana saja.
            </p>

            <div class="flex justify-center gap-4 flex-col sm:flex-row items-center">
                @if(auth()->check())
                    <a href="{{ route('filament.admin.pages.dashboard') }}"
                        class="px-8 py-3 bg-primary text-white rounded-xl font-bold text-lg hover:bg-amber-700 transition shadow-xl w-full sm:w-auto">
                        Buka Aplikasi
                    </a>
                @else
                    <a href="{{ route('filament.admin.auth.login') }}"
                        class="px-8 py-3 bg-primary text-white rounded-xl font-bold text-lg hover:bg-amber-700 transition shadow-xl shadow-amber-500/20 w-full sm:w-auto transform hover:-translate-y-1">
                        Login Admin
                    </a>

                    @if (Route::has('filament.admin.auth.register'))
                        <a href="{{ route('filament.admin.auth.register') }}"
                            class="px-8 py-3 bg-white text-gray-700 border border-gray-200 rounded-xl font-bold text-lg hover:bg-gray-50 transition w-full sm:w-auto">
                            Daftar Baru
                        </a>
                    @endif
                @endif
            </div>

            <div class="mt-16 relative rounded-xl bg-gray-900 p-2 shadow-2xl mx-auto max-w-5xl ring-1 ring-gray-900/10">
                <div
                    class="bg-gray-800 rounded-lg overflow-hidden aspect-video relative flex items-center justify-center">
                    <span class="text-gray-400 font-medium flex flex-col items-center">
                        <svg class="w-16 h-16 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                            </path>
                        </svg>
                        [Screenshot Filament Dashboard]
                    </span>
                </div>
            </div>
        </div>
    </section>

    <section id="fitur" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900">Mengapa InvenPOS?</h2>
                <p class="mt-4 text-gray-500">Dibangun dengan teknologi terbaru untuk kecepatan dan kemudahan.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Filament Power</h3>
                    <p class="text-gray-500">Admin panel yang responsif, cepat, dan mudah digunakan (User Friendly).</p>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Real-time Stock</h3>
                    <p class="text-gray-500">Stok berkurang otomatis saat terjadi transaksi di kasir.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Laporan Lengkap</h3>
                    <p class="text-gray-500">Export laporan penjualan ke PDF atau Excel dengan mudah.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-white border-t border-gray-100 pt-12 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-400">
            &copy; {{ date('Y') }} InvenPOS. Powered by Laravel & Filament.
        </div>
    </footer>
</body>

</html>