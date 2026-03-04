<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    // =============================================
    // API ENDPOINTS
    // =============================================

    #[OA\Get(
        path: "/api/profile",
        summary: "Pobierz dane profilu użytkownika (API)",
        tags: ["Profile"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Dane profilu",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "email", type: "string", format: "email"),
                        new OA\Property(property: "is_admin", type: "boolean"),
                        new OA\Property(property: "created_at", type: "string", format: "date-time"),
                    ]
                )
            ),
        ]
    )]
    public function apiShow(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => $user->is_admin,
            'created_at' => $user->created_at,
        ]);
    }

    #[OA\Patch(
        path: "/api/profile",
        summary: "Zaktualizuj dane profilu (API)",
        tags: ["Profile"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Nowa Nazwa"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "nowy@email.com"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Profil zaktualizowany",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Profile updated successfully"),
                        new OA\Property(property: "user", type: "object"),
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Błąd walidacji"),
        ]
    )]
    public function apiUpdate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $request->user()->id,
        ]);

        $user = $request->user();
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ]
        ]);
    }

    #[OA\Delete(
        path: "/api/profile",
        summary: "Usuń konto użytkownika (API)",
        tags: ["Profile"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["password"],
                properties: [
                    new OA\Property(property: "password", type: "string", format: "password", example: "current_password123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Konto usunięte",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Account deleted successfully")
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Nieprawidłowe hasło"),
        ]
    )]
    public function apiDestroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        
        // Usuń wszystkie tokeny użytkownika
        $user->tokens()->delete();
        
        // Usuń konto
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully'
        ]);
    }

    #[OA\Patch(
        path: "/api/profile/password",
        summary: "Zmień hasło użytkownika (API)",
        tags: ["Profile"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["current_password", "new_password"],
                properties: [
                    new OA\Property(property: "current_password", type: "string", format: "password", example: "old_password123"),
                    new OA\Property(property: "new_password", type: "string", format: "password", minLength: 8, example: "new_password456")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Hasło zostało zmienione",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Password updated successfully")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Błąd walidacji lub nieprawidłowe aktualne hasło",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Current password is incorrect")
                    ]
                )
            ),
        ]
    )]
    public function apiUpdatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully'
        ], 200);
    }

    // =============================================
    // WEB ENDPOINTS
    // =============================================

    /**
     * Display the user's profile form (BLADE).
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information (BLADE).
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's password (BLADE).
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('status', 'password-updated');
    }

    /**
     * Delete the user's account (BLADE).
     */
    public function destroy(Request $request): RedirectResponse
    {
        // PRZYWRÓĆ WALIDACJĘ HASŁA
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}