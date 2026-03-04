<section class="mb-4">
    <header>
        <h2 class="h5 font-weight-bold text-danger">
            {{ __('Usuń konto') }}
        </h2>

        <p class="text-secondary small">
            {{ __('Po usunięciu konta wszystkie jego zasoby i dane zostaną trwale usunięte. Przed usunięciem konta pobierz wszelkie dane, które chcesz zachować.') }}
        </p>
    </header>

    {{-- Przycisk otwierający modal --}}
    <button
        class="btn btn-red mt-3"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        {{ __('Usuń konto') }}
    </button>

    {{-- Modal --}}
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        {{-- Stylizacja wnętrza modala bezpośrednio, by pasowała do GAMELIST --}}
        <div class="p-4" style="background-color: #111; color: #f5f5f5; border: 1px solid #dc3545; border-radius: 12px;">
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <h2 class="h5 font-weight-bold text-danger">
                    {{ __('Czy na pewno chcesz usunąć konto?') }}
                </h2>

                <p class="text-secondary small">
                    {{ __('Wprowadź hasło, aby potwierdzić trwałe usunięcie konta.') }}
                </p>

                <div class="mt-4">
                    <label for="password" class="visually-hidden">{{ __('Hasło') }}</label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                        placeholder="{{ __('Hasło') }}"
                        style="background-color: #0f0f0f; border: 1px solid #333; color: #fff;"
                    />

                    @if($errors->userDeletion->has('password'))
                        <div class="text-danger small mt-2">
                            {{ $errors->userDeletion->first('password') }}
                        </div>
                    @endif
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    {{-- Przycisk Anuluj --}}
                    <button 
                        type="button" 
                        class="btn btn-outline-light me-3" 
                        x-on:click="$dispatch('close')"
                    >
                        {{ __('Anuluj') }}
                    </button>

                    {{-- Przycisk Potwierdź --}}
                    <button type="submit" class="btn btn-red">
                        {{ __('Usuń konto na zawsze') }}
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
</section>