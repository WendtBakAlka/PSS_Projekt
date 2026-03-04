@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4 text-light">Lista <span class="text-danger">Użytkowników</span></h2>

    <div class="admin-card" style="background: #111; border: 1px solid #1f1f1f; border-radius: 12px; overflow: hidden;">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr style="background: #1a1a1a;">
                    <th class="p-3">ID</th>
                    <th class="p-3">Użytkownik</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Rola</th>
                    <th class="p-3 text-end">Akcje</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr style="border-bottom: 1px solid #1f1f1f;">
                    <td class="p-3 text-secondary">#{{ $user->id }}</td>
                    <td class="p-3">{{ $user->name }}</td>
                    <td class="p-3 text-secondary">{{ $user->email }}</td>
                    <td class="p-3">
                        <span class="badge {{ $user->is_admin ? 'bg-danger' : 'bg-secondary' }}">
                            {{ $user->is_admin ? 'Admin' : 'User' }}
                        </span>
                    </td>
                    <td class="p-3 text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <form action="{{ route('admin.toggle-admin', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                    Zmień rolę
                                </button>
                            </form>
                            
                            @if($user->id !== auth()->id())
                            <form action="{{ route('admin.destroy', $user->id) }}" method="POST" 
                                  onsubmit="return confirm('Czy na pewno chcesz usunąć użytkownika {{ $user->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    Usuń
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection