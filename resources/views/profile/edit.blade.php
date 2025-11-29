<x-app-layout>
    <x-slot name="header">
        <h2 class="h3 mb-0 text-gray-800">
            {{ __('Account Settings') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            
            {{-- 1. PROFILE INFORMATION (Full width on large screens) --}}
            <div class="card shadow-lg mb-4 rounded-3">
                <div class="card-body p-4 p-md-5">
                    <div class="mx-auto" style="max-width: 800px;">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            {{-- 2. SECURITY & DELETION (Two-column responsive layout) --}}
            <div class="row">
                
                {{-- A. UPDATE PASSWORD --}}
                <div class="col-md-6 mb-4">
                    <div class="card shadow-lg h-100 rounded-3">
                        <div class="card-body p-4 p-md-4">
                            <div class="mx-auto" style="max-width: 600px;">
                                @include('profile.partials.update-password-form')
                            </div>
                        </div>
                    </div>
                </div>

                {{-- B. DELETE ACCOUNT (Visually distinct card) --}}
                <div class="col-md-6 mb-4">
                    <div class="card shadow-lg h-100 rounded-3 border border-danger border-2">
                        <div class="card-body p-4 p-md-4">
                            <div class="mx-auto" style="max-width: 600px;">
                                @include('profile.partials.delete-user-form')
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</x-app-layout>