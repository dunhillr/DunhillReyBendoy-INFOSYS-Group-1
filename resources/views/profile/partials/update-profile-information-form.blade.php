<section>
    <header class="border-bottom pb-3 mb-4">
        <h2 class="h4 font-weight-bold text-gray-900">
            {{ __('Personal Details') }}
        </h2>

        <p class="mt-1 text-secondary">
            {{ __("Update your profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4 row g-4">
        @csrf
        @method('patch')
        
        {{-- Name Field --}}
        <div class="col-md-6">
            <x-input-label for="name" :value="__('Name')" class="form-label fw-bold" />
            <x-text-input 
                id="name" 
                name="name" 
                type="text" 
                class="form-control rounded-lg" 
                :value="old('name', $user->name)" 
                required autofocus autocomplete="name" 
            />
            <x-input-error class="mt-2 text-danger" :messages="$errors->get('name')" />
        </div>

        {{-- Email Field --}}
        <div class="col-md-6">
            <x-input-label for="email" :value="__('Email')" class="form-label fw-bold" />
            <x-text-input 
                id="email" 
                name="email" 
                type="email" 
                class="form-control rounded-lg" 
                :value="old('email', $user->email)" 
                required autocomplete="username" 
            />
            <x-input-error class="mt-2 text-danger" :messages="$errors->get('email')" />

            {{-- Verification Message (Styled with Bootstrap alerts) --}}
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert alert-warning p-2 mt-3" role="alert">
                    <p class="text-sm">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="text-success mt-1">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>
        
        {{-- Action Buttons --}}
        <div class="col-12 d-flex align-items-center gap-3 pt-3">
            <x-primary-button class="btn btn-primary">{{ __('Save Changes') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-success"
                >{{ __('Saved successfully!') }}</p>
            @endif
        </div>
    </form>
</section>