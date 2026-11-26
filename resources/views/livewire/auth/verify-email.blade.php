<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="mt-4 flex flex-col gap-6">
  <div class="text-center text-sm text-gray-600">
    Merci de vérifier votre adresse e-mail en cliquant sur le lien que nous venons de vous envoyer.
  </div>

  @if (session('status') == 'verification-link-sent')
    <div class="text-center text-sm font-medium text-green-600">
      Un nouveau lien de vérification a été envoyé à l'adresse e-mail indiquée lors de votre inscription.
    </div>
  @endif

  <div class="flex flex-col items-center justify-between space-y-3">
    <flux:button wire:click="sendVerification" variant="primary" class="w-full">
      Renvoyer l'e-mail de vérification
    </flux:button>

    <button wire:click="logout" type="submit"
      class="focus:outline-hidden rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
      Se déconnecter
    </button>
  </div>
</div>
