@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <h2 class="mb-4 text-light">Panel <span class="text-danger">Admina</span></h2>

        <div class="row">
            <div class="col-md-4">
                <div class="admin-card p-4 text-center" style="background: #111; border: 1px solid #1f1f1f; border-radius: 12px;">
                    <h5 class="text-secondary">Użytkownicy</h5>
                    <a href="{{ route('admin.users') }}" class="btn btn-danger w-100">Zarządzaj Użytkownikami</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="admin-card p-4 text-center" style="background: #111; border: 1px solid #1f1f1f; border-radius: 12px;">
                    <h5 class="text-secondary">Gry</h5>
                    <a href="{{ route('admin.games') }}" class="btn btn-danger w-100">Przeglądaj Gry</a>
                </div>
            </div>
        </div>
    </div>
@endsection
