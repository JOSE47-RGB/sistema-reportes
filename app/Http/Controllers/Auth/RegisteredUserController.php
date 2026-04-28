<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // ✅ VALIDACIÓN PRINCIPAL
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ]);

        // ✅ VALIDACIÓN EXTRA - evitar similitud con nombre
        if (soundex($request->password) === soundex($request->name)) {
            throw ValidationException::withMessages([
                'password' => 'La contraseña no debe parecerse al nombre.',
            ]);
        }

        // ✅ VALIDACIÓN EXTRA - evitar similitud con email
        if (soundex($request->password) === soundex($request->email)) {
            throw ValidationException::withMessages([
                'password' => 'La contraseña no debe parecerse al correo.',
            ]);
        }

        // ✅ VALIDACIÓN EXTRA - evitar contraseñas débiles o con datos personales
        $badWords = [
            strtolower($request->name),
            strtolower($request->email),
            '123456',
            'password',
            'qwerty'
        ];

        foreach ($badWords as $word) {
            if (str_contains(strtolower($request->password), $word)) {
                throw ValidationException::withMessages([
                    'password' => 'La contraseña es muy débil o contiene datos personales.',
                ]);
            }
        }

        // ✅ CREAR USUARIO
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // ✅ EVENTO DE REGISTRO
        event(new Registered($user));

        // ✅ LOGIN AUTOMÁTICO
        Auth::login($user);

        // ✅ REDIRECCIÓN
        return redirect(route('dashboard', absolute: false));
    }
}