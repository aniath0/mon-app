{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grace Divine - {{ $title ?? 'Dashboard' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-bg { background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); }
        .nav-item.active { box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }
        .user-avatar { background: linear-gradient(to br, #e0e7ff, #c7d2fe); }
    </style>
</head>
<body class="antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar élégante -->
        <div class="w-64 sidebar-bg border-r border-gray-100/50 shadow-sm relative z-10">
            <!-- Logo et branding -->
            <div class="p-6 border-b border-gray-100/50">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg overflow-hidden shadow-sm">
    <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="w-full h-full object-cover">
</div>

                    <div>
                        <h1 class="text-lg font-semibold text-gray-900 tracking-tight">Grace Divine</h1>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Laboratoire Médical</p>
                    </div>
                </div>
            </div>

            <!-- Navigation minimaliste -->
            <nav class="p-4 space-y-1">
                <a href="{{ route('dashboard') }}" 
                   class="nav-item flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'active text-indigo-700 bg-indigo-50/50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50/50' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    Tableau de bord
                </a>
                
                <a href="/patients" 
                   class="nav-item flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->is('patients*') ? 'active text-indigo-700 bg-indigo-50/50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50/50' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.67 3.623a10.95 10.95 0 01-.67 3.623"></path>
                    </svg>
                    Patients
                </a>

                <a href="/exams" 
                   class="nav-item flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->is('exams*') ? 'active text-indigo-700 bg-indigo-50/50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50/50' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Examens
                </a>
                
                <a href="/resultats/create" 
                   class="nav-item flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->is('resultats/create') ? 'active text-indigo-700 bg-indigo-50/50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50/50' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nouveau résultat
                </a>
                
                <a href="/resultats" 
                   class="nav-item flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->is('resultats*') && !request()->is('resultats/create') ? 'active text-indigo-700 bg-indigo-50/50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50/50' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Historique
                </a>
                
                
            <!-- User profile minimaliste -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-100/50 bg-white/30 backdrop-blur-sm">
                <div class="flex items-center space-x-3">
                    <div class="user-avatar w-10 h-10 rounded-full flex items-center justify-center">
                        <span class="text-sm font-semibold text-indigo-700">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2 text-gray-400 hover:text-gray-600 transition-colors rounded-lg hover:bg-gray-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="flex-1">
            <!-- Navbar simple -->
            <header class="bg-white border-b border-gray-100 shadow-sm">
                <div class="px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-medium text-gray-900">{{ $header ?? 'Dashboard' }}</h2>
                        <div class="flex items-center space-x-4">
                            <!-- Notification button -->
                            <button class="p-2 text-gray-400 hover:text-gray-500 rounded-lg hover:bg-gray-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </button>
                            <!-- Profile dropdown -->
                            <div class="relative">
                                <button class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
                                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-xs font-medium">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                    </div>
                                    <span class="text-sm font-medium hidden sm:inline">{{ auth()->user()->name }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main content area -->
            <main class="flex-1 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>