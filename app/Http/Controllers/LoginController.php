<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class LoginController extends Controller
{
    /**
     * Mostrar el formulario de inicio de sesión.
     */
    public function create()
    {
        if (session('id_usuario')) {
            return redirect()->route('inicio');
        }
        return view('auth.login');
    }

    /**
     * Validar usuario y contraseña (RF-01).
     */
    public function store(Request $request)
    {
        $request->validate([
            'usuario'    => 'required',
            'contrasena' => 'required',
        ]);

        $usuario = Usuario::with('rol')->where('usuario', $request->usuario)->first();

        // El usuario no existe
        if (! $usuario) {
            return back()->withErrors(['usuario' => 'Usuario o contraseña incorrectos.'])->withInput();
        }

        // Cuenta desactivada por el administrador
        if (! $usuario->activo) {
            return back()->withErrors(['usuario' => 'Esta cuenta está desactivada. Comunícate con el administrador.'])->withInput();
        }

        // Cuenta bloqueada por 5 intentos fallidos
        if ($usuario->bloqueado) {
            return back()->withErrors(['usuario' => 'Cuenta bloqueada por intentos fallidos. Pide al administrador que la desbloquee.'])->withInput();
        }

        // Contraseña incorrecta: se suma un intento y a los 5 se bloquea
        if (! Hash::check($request->contrasena, $usuario->contrasena_hash)) {
            $usuario->intentos_fallidos = $usuario->intentos_fallidos + 1;

            if ($usuario->intentos_fallidos >= 5) {
                $usuario->bloqueado = true;
                $usuario->save();
                return back()->withErrors(['usuario' => 'Cuenta bloqueada por 5 intentos fallidos. Pide al administrador que la desbloquee.'])->withInput();
            }

            $usuario->save();
            $quedan = 5 - $usuario->intentos_fallidos;
            return back()->withErrors(['usuario' => "Usuario o contraseña incorrectos. Te quedan {$quedan} intentos."])->withInput();
        }

        // Datos correctos: se reinician los intentos y se guarda la sesión
        $usuario->intentos_fallidos = 0;
        $usuario->save();

        $request->session()->regenerate();
        session([
            'id_usuario' => $usuario->id_usuario,
            'nombre'     => $usuario->nombre,
            'rol'        => $usuario->rol->nombre,
        ]);

        return redirect()->route('inicio');
    }

    /**
     * Página de inicio según el rol.
     */
    public function inicio()
    {
        if (! session('id_usuario')) {
            return redirect()->route('login');
        }
        return view('inicio');
    }

    /**
     * Cerrar sesión.
     */
    public function destroy(Request $request)
    {
        $request->session()->flush();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Sesión cerrada.');
    }
}