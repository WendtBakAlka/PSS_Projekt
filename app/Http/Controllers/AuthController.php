<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: "/api/auth/login",
        summary: "Zaloguj się przez API (otrzymaj token Sanctum)",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Dane logowania",
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "admin@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "haslo123"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Zalogowano pomyślnie, zwrócony token",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "token", type: "string", example: "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ1234567890abcdef")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Nieprawidłowe dane logowania"),
            new OA\Response(response: 422, description: "Błąd walidacji"),
        ]
    )]
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Nieprawidłowe dane logowania.'],
            ]);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    #[OA\Post(
        path: "/api/auth/register",
        summary: "Zarejestruj nowego użytkownika przez API",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Dane rejestracji",
            content: new OA\JsonContent(
                required: ["name", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Jan Kowalski"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "jan@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", minLength: 8, example: "tajnehaslo123"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "tajnehaslo123"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Użytkownik zarejestrowany",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "user", type: "object"),
                        new OA\Property(property: "token", type: "string")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Błąd walidacji (np. email już istnieje)"),
        ]
    )]
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    #[OA\Post(
        path: "/api/auth/logout",
        summary: "Wyloguj użytkownika (unieważnij token Sanctum)",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Wylogowano pomyślnie",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Wylogowano.")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Nieautoryzowany (brak tokena)"),
        ]
    )]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Wylogowano.']);
    }

    // METODY WEB (bez adnotacji Swaggera - bo to nie API!)
    public function webLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/games');
        }

        return back()->withErrors([
            'email' => 'Nieprawidłowe dane logowania.',
        ])->onlyInput('email');
    }

    public function webRegister(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/games');
    }

    public function webLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}