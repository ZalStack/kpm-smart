<nav x-data="{ open: false }" class="bg-navy shadow-lg sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-lg font-bold text-white hover:text-gold-400 transition duration-300">
                        <span class="text-2xl">📚</span>
                        <span>KPM Belajar Online</span>
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <div class="hidden md:flex items-center gap-2 ms-8">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dasbor') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden md:flex md:items-center md:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-white/80 bg-white/10 hover:text-gold-400 hover:bg-white/20 focus:outline-none transition duration-200">
                            <span class="w-8 h-8 rounded-full bg-accent-400 flex items-center justify-center text-sm font-bold text-white">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</span>
                            <div>{{ Str::limit(Auth::user()->name, 15) }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-border">
                            <p class="text-sm font-bold text-gray-800">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ Auth::user()->email }}</p>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">
                            👤 {{ __('Profil Saya') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <div class="border-t border-border mt-1 pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    🚪 {{ __('Keluar') }}
                                </x-dropdown-link>
                            </form>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center md:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-white/10 text-white hover:bg-white/20 focus:outline-none transition duration-200 border border-white/10">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-white/10 bg-navy shadow-2xl">
        <div class="px-4 py-3 space-y-1">
            <!-- User Info -->
            <div class="flex items-center gap-3 px-3 py-3 rounded-xl bg-white/5 mb-2">
                <span class="w-10 h-10 rounded-full bg-accent-400 flex items-center justify-center text-lg font-bold text-white flex-shrink-0">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</span>
                <div class="min-w-0">
                    <p class="font-semibold text-white truncate">{{ Auth::user()->name ?? 'Pengguna' }}</p>
                    <p class="text-xs text-white/50 truncate">{{ Auth::user()->email ?? '' }}</p>
                </div>
            </div>

            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                🏠 {{ __('Dasbor') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('profile.edit')">
                👤 {{ __('Profil Saya') }}
            </x-responsive-nav-link>

            <!-- Authentication -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                    this.closest('form').submit();">
                    🚪 {{ __('Keluar') }}
                </x-responsive-nav-link>
            </form>
        </div>
    </div>
</nav>
