<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6">
  <div class="relative mb-5">
    <flux:heading>Supprimer le compte</flux:heading>
    <flux:subheading>Supprimer définitivement votre compte</flux:subheading>
  </div>

  <flux:modal.trigger name="confirm-user-deletion">
    <flux:button variant="danger" x-data=""
      x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
      Supprimer le compte
    </flux:button>
  </flux:modal.trigger>

  <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
    <form wire:submit="deleteUser" class="space-y-6">
      <div>
        <flux:heading size="lg">Voulez-vous vraiment supprimer votre compte ?</flux:heading>

        <flux:subheading>
          Cette action est définitive. Saisissez votre mot de passe pour confirmer la suppression de votre compte.
        </flux:subheading>
      </div>

      <flux:input wire:model="password" id="password" label="Mot de passe" type="password" name="password" />

      <div class="flex justify-end space-x-2">
        <flux:modal.close>
          <flux:button variant="filled">Annuler</flux:button>
        </flux:modal.close>

        <flux:button variant="danger" type="submit">Supprimer le compte</flux:button>
      </div>
    </form>
  </flux:modal>
</section>
