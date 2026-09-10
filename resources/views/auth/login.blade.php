<x-guest-layout>
    <!-- Message de session -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" value="Adresse email" />
            <x-text-input
                id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="exemple@email.com"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Mot de passe -->
        <div>
            <x-input-label for="password" value="Mot de passe" />
            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Se souvenir de moi -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    name="remember"
                >
                <span class="ms-2 text-sm text-gray-600">
                    Se souvenir de moi
                </span>
            </label>

            @if (Route::has('password.request'))
                <a
                    class="underline text-sm text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    href="{{ route('password.request') }}"
                >
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        <!-- Bouton -->
        <div>
            <x-primary-button class="w-full justify-center">
                Se connecter
            </x-primary-button>
        </div>
    </form>

    <!-- Inscription -->
    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
            Pas encore de compte ?
            <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-700 font-medium underline">
                S’inscrire
            </a>
        </p>
    </div>
</x-guest-layout>
