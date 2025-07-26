<?php

use App\Livewire\Actions\Logout;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public string $password = '';
    public bool $confirmUserDeletion = false;

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $this->authorize('user.delete');

        $user = Auth::user();

        if ($user->avatar) {
            $path = str($user->avatar)->after('/storage/');
            \Storage::disk('public')->delete($path);
        }

        tap($user, $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }

    public function exception(Throwable $e, $stopPropagation): void
    {
        if ($e instanceof AuthorizationException) {
            $this->confirmUserDeletion = false;

            $this->error($e->getMessage());

            $stopPropagation();
        }
    }
}; ?>

<section class="mt-10 space-y-6">
    <x-partials.header-title :heading="__('Delete account')"
                             :subheading="__('Delete your account and all of its resources')" size="md"/>

    <x-mary-modal wire:model="confirmUserDeletion" :title="__('Are you sure you want to delete your account?')"
                  :subtitle="__(
        'Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.',
    )" class="backdrop-blur">
        <x-mary-form wire:submit="deleteUser" no-separator>
            <x-mary-password wire:model="password" :label="__('Password')" required right/>

            <x-slot:actions>
                <x-mary-button :label="__('Cancel')" @click="$wire.confirmUserDeletion = false" class="btn-soft"/>
                <x-mary-button type="submit" :label="__('Delete account')" class="btn-error"/>
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>

    <x-mary-button class="btn-error" :label="__('Delete account')" @click="$wire.confirmUserDeletion = true"/>
</section>
