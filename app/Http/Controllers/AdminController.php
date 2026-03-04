<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class AdminController extends Controller
{
    // =============================================
    // WEB ENDPOINTS (oryginalne - bez zmian)
    // =============================================
    
    /**
     * Główny pulpit admina (admin.blade.php)
     */
    public function index()
    {
        return view('admin.admin');
    }

    /**
     * Lista wszystkich użytkowników (admin-users.blade.php)
     */
    #[OA\Get(
        path: "/api/admin/users",
        summary: "Pobierz listę wszystkich użytkowników (API)",
        tags: ["Admin"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista użytkowników",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "users",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "name", type: "string"),
                                    new OA\Property(property: "email", type: "string", format: "email"),
                                    new OA\Property(property: "is_admin", type: "boolean"),
                                    new OA\Property(property: "created_at", type: "string", format: "date-time"),
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Brak uprawnień administratora"),
        ]
    )]
    public function listUsers(Request $request)
    {
        $users = User::select('id', 'name', 'email', 'is_admin', 'created_at')->get();

        // Jeśli zapytanie chce JSON (API), dajemy JSON
        if ($request->expectsJson()) {
            return response()->json(['users' => $users], 200);
        }

        // Jeśli to web (przeglądarka) - zwróć widok
        return view('admin.admin-users', compact('users'));
    }

    /**
     * Przełączanie roli admina (Toggle) - DODANO ZABEZPIECZENIE
     */
    public function toggleAdmin($id)
    {
        $user = User::findOrFail($id);

        // Nie możesz zmienić uprawnień samemu sobie
        if ($id == Auth::id()) {
            // SPRAWDZAMY CZY TO OSTATNI ADMIN
            $adminCount = User::where('is_admin', true)->count();
            
            if ($adminCount <= 1 && $user->is_admin) {
                return back()->with('error', 'Nie możesz odebrać sobie uprawnień admina, ponieważ jesteś ostatnim administratorem w systemie!');
            }
            
            return back()->with('error', 'Nie możesz zmienić uprawnień samemu sobie.');
        }

        // SPRAWDZAMY CZY UŻYTKOWNIK DO ZMIANY JEST OSTATNIM ADMINEM
        if ($user->is_admin) {
            $adminCount = User::where('is_admin', true)->count();
            
            if ($adminCount <= 1) {
                return back()->with('error', 'Nie można odebrać uprawnień ostatniemu administratorowi w systemie!');
            }
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        $action = $user->is_admin ? 'nadano' : 'odebrano';
        return back()->with('success', "Uprawnienia administratora zostały {$action} użytkownikowi {$user->name}.");
    }

    /**
     * Usuwanie użytkownika - DODANO ZABEZPIECZENIE
     */
    #[OA\Delete(
        path: "/api/admin/users/{id}",
        summary: "Usuń użytkownika (API)",
        tags: ["Admin"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID użytkownika do usunięcia",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Użytkownik usunięty",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Użytkownik usunięty.")
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Brak uprawnień lub próba usunięcia własnego konta"),
            new OA\Response(response: 404, description: "Użytkownik nie znaleziony"),
        ]
    )]
    public function deleteUser(Request $request, $id)
    {
        // Nie możesz usunąć własnego konta
        if ($id == Auth::id()) {
            $msg = 'Nie możesz usunąć własnego konta.';
            return $request->expectsJson() 
                ? response()->json(['message' => $msg], 403) 
                : back()->with('error', $msg);
        }

        $user = User::findOrFail($id);
        
        // SPRAWDZAMY CZY UŻYTKOWNIK DO USUNIĘCIA JEST OSTATNIM ADMINEM
        if ($user->is_admin) {
            $adminCount = User::where('is_admin', true)->count();
            
            if ($adminCount <= 1) {
                $msg = 'Nie można usunąć ostatniego administratora w systemie!';
                return $request->expectsJson() 
                    ? response()->json(['message' => $msg], 403) 
                    : back()->with('error', $msg);
            }
        }

        $userName = $user->name;
        $user->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Użytkownik usunięty.'], 200);
        }

        return redirect()->route('admin.users')->with('success', "Użytkownik {$userName} został usunięty.");
    }

    // =============================================
    // DODATKOWE API ENDPOINTS (brakujące)
    // =============================================

    #[OA\Get(
        path: "/api/admin/users/{id}",
        summary: "Pobierz szczegóły użytkownika (API)",
        tags: ["Admin"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID użytkownika",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Szczegóły użytkownika",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "email", type: "string", format: "email"),
                        new OA\Property(property: "is_admin", type: "boolean"),
                        new OA\Property(property: "created_at", type: "string", format: "date-time"),
                        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Brak uprawnień administratora"),
            new OA\Response(response: 404, description: "Użytkownik nie znaleziony"),
        ]
    )]
    public function apiShowUser($id)
    {
        $user = User::select('id', 'name', 'email', 'is_admin', 'created_at', 'updated_at')
                    ->findOrFail($id);
        return response()->json(['user' => $user]);
    }

    #[OA\Patch(
        path: "/api/admin/users/{id}",
        summary: "Zaktualizuj dane użytkownika (API)",
        tags: ["Admin"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID użytkownika",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Nowa Nazwa"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "nowy@email.com"),
                    new OA\Property(property: "is_admin", type: "boolean", example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Użytkownik zaktualizowany",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Użytkownik zaktualizowany"),
                        new OA\Property(property: "user", type: "object"),
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Brak uprawnień administratora"),
            new OA\Response(response: 404, description: "Użytkownik nie znaleziony"),
            new OA\Response(response: 422, description: "Błąd walidacji"),
        ]
    )]
    public function apiUpdateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'is_admin' => 'sometimes|boolean',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Użytkownik zaktualizowany',
            'user' => $user
        ]);
    }

    #[OA\Patch(
        path: "/api/admin/users/{id}/toggle-admin",
        summary: "Przełącz rolę administratora (API)",
        tags: ["Admin"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID użytkownika",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Rola administratora zmieniona",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(property: "user", type: "object"),
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Brak uprawnień lub próba zmiany własnej roli"),
            new OA\Response(response: 404, description: "Użytkownik nie znaleziony"),
        ]
    )]
    public function apiToggleAdmin($id)
    {
        $user = User::findOrFail($id);

        // Nie możesz zmienić uprawnień samemu sobie
        if ($id == Auth::id()) {
            $adminCount = User::where('is_admin', true)->count();
            
            if ($adminCount <= 1 && $user->is_admin) {
                return response()->json([
                    'message' => 'Nie możesz odebrać sobie uprawnień admina, ponieważ jesteś ostatnim administratorem w systemie!'
                ], 403);
            }
            
            return response()->json([
                'message' => 'Nie możesz zmienić uprawnień samemu sobie.'
            ], 403);
        }

        // SPRAWDZAMY CZY UŻYTKOWNIK DO ZMIANY JEST OSTATNIM ADMINEM
        if ($user->is_admin) {
            $adminCount = User::where('is_admin', true)->count();
            
            if ($adminCount <= 1) {
                return response()->json([
                    'message' => 'Nie można odebrać uprawnień ostatniemu administratorowi w systemie!'
                ], 403);
            }
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        $action = $user->is_admin ? 'nadano' : 'odebrano';
        
        return response()->json([
            'message' => "Uprawnienia administratora zostały {$action} użytkownikowi {$user->name}.",
            'user' => $user
        ]);
    }
}