<section>
    <header>
        <h2 class="h5 font-weight-bold text-danger">
            {{ __('Zmiana hasła') }}
        </h2>

        <p class="text-secondary small">
            {{ __('Upewnij się, że Twoje konto używa długiego, losowego hasła, aby zachować bezpieczeństwo.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-4">
        @csrf
        @method('put')

        {{-- Obecne hasło --}}
        <div class="mb-3">
            <label for="update_password_current_password" class="form-label text-light small">{{ __('Obecne hasło') }}</label>
            <input 
                id="update_password_current_password" 
                name="current_password" 
                type="password" 
                class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                autocomplete="current-password"
            >
            @if($errors->updatePassword->has('current_password'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
            @endif
        </div>

        {{-- Nowe hasło --}}
        <div class="mb-3">
            <label for="update_password_password" class="form-label text-light small">{{ __('Nowe hasło') }}</label>
            <input 
                id="update_password_password" 
                name="password" 
                type="password" 
                class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                autocomplete="new-password"
            >
            @if($errors->updatePassword->has('password'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
            @endif
        </div>

        {{-- Potwierdź nowe hasło --}}
        <div class="mb-4">
            <label for="update_password_password_confirmation" class="form-label text-light small">{{ __('Potwierdź nowe hasło') }}</label>
            <input 
                id="update_password_password_confirmation" 
                name="password_confirmation" 
                type="password" 
                class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" 
                autocomplete="new-password"
            >
            @if($errors->updatePassword->has('password_confirmation'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-red px-4">
                {{ __('Zapisz hasło') }}
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-success small mb-0"
                >
                    <i class="bi bi-check-lg"></i> {{ __('Zapisano pomyślnie.') }}
                </p>
            @endif
        </div>
    </form>
</section>