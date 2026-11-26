<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $this->ensureIsNotRateLimited();
        RateLimiter::hit($this->throttleKey(), 3600);

        $validated = $this->validate([
            // RG-01 : pseudo unique de 3 à 20 caractères
            'username' => ['required', 'string', 'min:3', 'max:20', 'alpha_dash:ascii', 'unique:' . User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 10)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => 'Trop de tentatives d\'inscription. Veuillez réessayer dans ' . ceil($seconds / 60) . ' minutes.',
        ]);
    }

    protected function throttleKey(): string
    {
        return 'register|' . request()->ip();
    }
}; ?>

<div class="flex flex-col gap-6">
  <x-auth-header title="Créer un compte" description="Renseignez les informations ci-dessous pour créer votre compte" />

  <!-- Session Status -->
  <x-auth-session-status class="text-center" :status="session('status')" />

  <form wire:submit="register" class="flex flex-col gap-6">
    <!-- Pseudo -->
    <div class="grid gap-2">
      <flux:input wire:model="username" id="username" label="Pseudo" type="text" name="username" required autofocus
        autocomplete="username" placeholder="Pseudo" description="3 à 20 caractères : lettres, chiffres, - et _" />
    </div>

    <!-- Email Address -->
    <div class="grid gap-2">
      <flux:input wire:model="email" id="email" label="Adresse e-mail" type="email" name="email" required
        autocomplete="email" placeholder="email@exemple.fr" />
    </div>

    <!-- Password -->
    <div class="grid gap-2">
      <flux:input wire:model="password" id="password" label="Mot de passe" type="password" name="password" required
        autocomplete="new-password" placeholder="Mot de passe"
        description="8 caractères minimum, dont une majuscule, une minuscule et un chiffre" />
    </div>

    <!-- Confirm Password -->
    <div class="grid gap-2">
      <flux:input wire:model="password_confirmation" id="password_confirmation" label="Confirmer le mot de passe"
        type="password" name="password_confirmation" required autocomplete="new-password"
        placeholder="Confirmer le mot de passe" />
    </div>

    <div class="flex items-center justify-end">
      <flux:button type="submit" variant="primary" class="w-full">
        Créer mon compte
      </flux:button>
    </div>
  </form>

  <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
    Vous avez déjà un compte ?
    <x-text-link href="{{ route('login') }}">Se connecter</x-text-link>
  </div>
</div>
