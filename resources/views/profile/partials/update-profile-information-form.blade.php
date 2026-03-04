<section>
    <header>
        <h2 class="h5 font-weight-bold text-danger">
            {{ __('Informacje o profilu') }}
        </h2>

        <p class="text-secondary small">
            {{ __("Zaktualizuj informacje profilowe swojego konta oraz adres e-mail.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4">
        @csrf
        @method('patch')

        {{-- Nazwa użytkownika --}}
        <div class="mb-3">
            <label for="name" class="form-label text-light small">{{ __('Nazwa') }}</label>
            <input 
                id="name" 
                name="name" 
                type="text" 
                class="form-control @error('name') is-invalid @enderror" 
                value="{{ old('name', $user->name) }}" 
                required 
                autofocus 
                autocomplete="name"
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label text-light small">{{ __('Email') }}</label>
            <input 
                id="email" 
                name="email" 
                type="email" 
                class="form-control @error('email') is-invalid @enderror" 
                value="{{ old('email', $user->email) }}" 
                required 
                autocomplete="username"
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            {{-- Obsługa weryfikacji e-maila --}}
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-warning small">
                        {{ __('Twój adres e-mail jest niezweryfikowany.') }}

                        <button form="send-verification" class="btn btn-link btn-sm p-0 text-danger text-decoration-none hover:text-light">
                            {{ __('Kliknij tutaj, aby wysłać ponownie e-mail weryfikacyjny.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="text-success small font-weight-bold">
                            {{ __('Nowy link weryfikacyjny został wysłany na Twój adres e-mail.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-red px-4">
                {{ __('Zapisz zmiany') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-success small mb-0"
                >
                    {{ __('Zapisano.') }}
                </p>
            @endif
        </div>
    </form>
</section>