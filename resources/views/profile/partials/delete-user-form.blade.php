<section class="space-y-4">
    <header class="border-bottom border-danger pb-3 mb-4">
        <h2 class="h4 font-weight-bold text-danger">
            {{ __('Permanently Delete Account') }}
        </h2>

        <p class="mt-1 text-secondary">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button
        class="btn btn-danger"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-4 p-md-5">
            @csrf
            @method('delete')

            <h2 class="h5 font-weight-bold text-danger mb-3">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="text-secondary">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-4">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="form-control"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-danger" />
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <x-secondary-button class="btn btn-secondary" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="btn btn-danger ms-3">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>