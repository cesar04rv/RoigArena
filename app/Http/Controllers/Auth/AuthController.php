<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /* Registrar un nuevo usuario*/
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => false,
        ]);

        // Crear token para API
        $token = $user->createToken('auth-token')->plainTextToken;

        // Si es una petición web (desde el formulario), iniciar sesión y redirigir
        if ($request->routeIs('register.post')) {
            Auth::login($user);
            $request->session()->regenerate();
            
            return redirect('/')
                ->with('success', '¡Registro exitoso! Bienvenido/a ' . $user->nombre);
        }

        // Si es API, devolver JSON
        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function loginApi(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Las credenciales son incorrectas.',
            ], 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function loginWeb(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return back()
                ->withErrors(['email' => 'Las credenciales son incorrectas.'])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        $user = $request->user();
        
        // Crear token para uso en la SPA/API si es necesario
        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        // Si es admin, redirigir al panel de admin
        if ($user->is_admin) {
            return redirect()->route('admin.eventos.create')
                ->with('success', '¡Bienvenido/a ' . $user->nombre . '!');
        }

        return redirect('/')
            ->with('success', '¡Bienvenido/a ' . $user->nombre . '!');
    }

    /**
     * Obtener datos del usuario autenticado
     */
    public function user(Request $request)
    {
        return response()->json([
            'data' => $request->user(),
        ]);
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        // Eliminar todos los tokens del usuario
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
        ]);
    }

    public function logoutWeb(Request $request)
    {
        $request->user()?->tokens()->delete();

        // Cerrar sesión
        Auth::logout();

        $request->session()->regenerateToken();

        $request->session()->invalidate();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Sesión cerrada correctamente']);
        }

        // Redirigir a la página de inicio
        return redirect()->route('home')->with('success', 'Sesión cerrada correctamente');
    }
}
