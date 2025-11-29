<section>
    <header class="border-bottom pb-3 mb-4">
        <h2 class="h4 font-weight-bold text-gray-900">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-secondary">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-4 space-y-4">
        @csrf
        @method('put')

        {{-- 1. Current Password --}}
        <div x-data="{ show: false }">
            <x-input-label for="update_password_current_password" :value="__('Current Password')" class="form-label fw-bold" />
            
            <div class="input-group">
                {{-- Input: added border-end-0 --}}
                <input 
                    id="update_password_current_password" 
                    name="current_password" 
                    :type="show ? 'text' : 'password'" 
                    class="form-control border-end-0" 
                    autocomplete="current-password"
                >
                {{-- Button: White BG, No left border, Gray text. 'btn' gives default hover. --}}
                <button type="button" class="btn bg-white border border-start-0 text-secondary" @click="show = !show">
                    <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-danger" />
        </div>

        {{-- 2. New Password --}}
        <div x-data="{ show: false }">
            <x-input-label for="update_password_password" :value="__('New Password')" class="form-label fw-bold" />
            
            <div class="input-group">
                {{-- Input: added border-end-0 --}}
                <input 
                    id="update_password_password" 
                    name="password" 
                    :type="show ? 'text' : 'password'" 
                    class="form-control border-end-0" 
                    autocomplete="new-password"
                >
                {{-- Button: White BG, No left border, Gray text. --}}
                <button type="button" class="btn bg-white border border-start-0 text-secondary" @click="show = !show">
                    <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-danger" />
        </div>

        {{-- 3. Confirm Password (No toggle) --}}
        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" class="form-label fw-bold" />
            
            <x-text-input 
                id="update_password_password_confirmation" 
                name="password_confirmation" 
                type="password" 
                class="form-control" 
                autocomplete="new-password" 
            />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-danger" />
        </div>

        <div class="d-flex align-items-center gap-3 pt-3">
            <x-primary-button class="btn btn-primary">{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-success"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>