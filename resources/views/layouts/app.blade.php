<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Finance Tracker') }} - @yield('title')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- DARK THEME ONLY - Forced -->
    <style>
        /* Force dark theme always */
        html, body {
            background-color: #111827 !important;
            color: #f3f4f6 !important;
            overflow-x: hidden !important;
            max-width: 100vw !important;
        }
        
        /* Prevent horizontal scroll */
        * {
            max-width: 100%;
        }
        
        /* Cards - dark theme only */
        .card {
            background-color: #1f2937 !important;
            color: #f3f4f6 !important;
            border: 1px solid #374151 !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.3) !important;
            padding: 1.5rem !important;
        }
        
        /* Canvas transparency */
        canvas {
            background-color: transparent !important;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-900 text-gray-100" data-page="@yield('page', 'default')">
    
    <!-- Navbar -->
    <nav class="fixed top-0 z-50 w-full bg-gray-800 border-b border-gray-700 shadow-sm">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start rtl:justify-end">
                    <!-- Mobile menu button -->
                    <button data-drawer-target="sidebar" data-drawer-toggle="sidebar" aria-controls="sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-400 rounded-lg sm:hidden hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-600">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                        </svg>
                    </button>
                    
                    <!-- Logo -->
                    <a href="{{ route('dashboard') }}" class="flex ms-2 md:me-24">
                        <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white ms-2">Finance Tracker</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-3">
                    <!-- User dropdown -->
                    <button type="button" class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600" id="user-menu-button" data-dropdown-toggle="user-dropdown" data-dropdown-placement="bottom">
                        <span class="sr-only">Open user menu</span>
                        <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-medium">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </button>
                    
                    <!-- Dropdown menu -->
                    <div class="z-50 hidden my-4 text-base list-none bg-gray-700 divide-y divide-gray-600 rounded shadow-lg border border-gray-600" id="user-dropdown">
                        <div class="px-4 py-3">
                            <p class="text-sm text-white font-medium">{{ auth()->user()->name }}</p>
                            <p class="text-sm text-gray-300 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <ul class="py-1">
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-200 hover:bg-gray-600 hover:text-white font-medium">
                                        Вийти
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-gray-800 border-r border-gray-700 sm:translate-x-0 shadow-sm">
        <div class="h-full px-3 pb-4 overflow-y-auto">
            <ul class="space-y-2 font-medium">
                <li>
                    <a href="{{ route('dashboard') }}" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 group {{ request()->routeIs('dashboard') ? 'bg-gray-700 font-semibold' : '' }}">
                        <svg class="w-5 h-5 text-gray-400 transition duration-75 group-hover:text-white" fill="currentColor" viewBox="0 0 22 21">
                            <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
                            <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
                        </svg>
                        <span class="ms-3">Дашборд</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('transactions.index') }}" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 group {{ request()->routeIs('transactions.*') ? 'bg-gray-700 font-semibold' : '' }}">
                        <svg class="w-5 h-5 text-gray-400 transition duration-75 group-hover:text-white" fill="currentColor" viewBox="0 0 18 20">
                            <path d="M17 5.923A1 1 0 0 0 16 5h-3V4a4 4 0 1 0-8 0v1H2a1 1 0 0 0-1 .923L.086 17.846A2 2 0 0 0 2.08 20h13.84a2 2 0 0 0 1.994-2.153L17 5.923ZM7 9a1 1 0 0 1-2 0V7h2v2Zm0-5a2 2 0 1 1 4 0v1H7V4Zm6 5a1 1 0 1 1-2 0V7h2v2Z"/>
                        </svg>
                        <span class="ms-3">Транзакції</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('categories.index') }}" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 group {{ request()->routeIs('categories.*') ? 'bg-gray-700 font-semibold' : '' }}">
                        <svg class="w-5 h-5 text-gray-400 transition duration-75 group-hover:text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-8a2 2 0 00-2-2H5zm0 2h8v8H5V7z"/>
                        </svg>
                        <span class="ms-3">Категорії</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('budgets.index') }}" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 group {{ request()->routeIs('budgets.*') ? 'bg-gray-700 font-semibold' : '' }}">
                        <svg class="w-5 h-5 text-gray-400 transition duration-75 group-hover:text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/>
                            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/>
                        </svg>
                        <span class="ms-3">Бюджети</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('hours-calculator.index') }}" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 group {{ request()->routeIs('hours-calculator.*') ? 'bg-gray-700 font-semibold' : '' }}">
                        <svg class="w-5 h-5 text-gray-400 transition duration-75 group-hover:text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ms-3">Калькулятор годин</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    <!-- Main content -->
    <div class="p-4 sm:ml-64 pt-20">
        @if (session('success'))
            <div class="mb-4 p-4 text-sm text-green-400 rounded-lg bg-gray-800" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 text-sm text-red-400 rounded-lg bg-gray-800" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    @stack('scripts')

</body>
</html>
