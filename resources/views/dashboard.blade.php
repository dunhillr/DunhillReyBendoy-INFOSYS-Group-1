<x-app-layout>
    <x-slot name="header">
        <h2 class="h4">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-body p-4 text-dark">
                        <h3 class="card-title h5">Welcome, {{ Auth::user()->name }}!</h3>
                        <p class="mt-2">You’re logged in!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
