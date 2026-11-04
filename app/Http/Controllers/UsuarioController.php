<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    /**
     * Lista de usuarios (solo el administrador).
     */
    public function index()
    {
        abort_unless(session('rol') === 'ADMINISTRADOR', 403);

        $usuarios = Usuario::with('rol')->orderBy('nombre')->get();
        return view('usuarios.index', compact('usuarios'));
    }

    /**
     * Formulario para crear un usuario.
     */
    public function create()
    {
        abort_unless(session('rol') === 'ADMINISTRADOR', 403);

        $roles = Rol::all(['id_rol', 'nombre']);
        return view('usuarios.create', compact('roles'));
    }

    /**
     * Guardar el usuario nuevo.
     */
    public function store(Request $request)
    {
        abort_unless(session('rol') === 'ADMINISTRADOR', 403);

        $request->validate([
            'nombre'     => 'required|max:100',
            'usuario'    => 'required|max:50|unique:usuarios,usuario',
            'contrasena' => 'required|min:6|max:255',
            'id_rol'     => 'required|exists:roles,id_rol',
        ]);

        // Crear el usuario en la base de datos (la contraseña se guarda cifrada)
        Usuario::create([
            'nombre'          => $request->nombre,
            'usuario'         => $request->usuario,
            'contrasena_hash' => Hash::make($request->contrasena),
            'id_rol'          => $request->id_rol,
            'activo'          => true,
            'bloqueado'       => false,
            'intentos_fallidos' => 0,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Formulario para editar un usuario.
     */
    public function edit(Usuario $usuario)
    {
        abort_unless(session('rol') === 'ADMINISTRADOR', 403);

        $roles = Rol::all(['id_rol', 'nombre']);
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    /**
     * Guardar los cambios del usuario.
     * Aquí también se activa/desactiva y se desbloquea la cuenta (RF-11).
     */
    public function update(Request $request, Usuario $usuario)
    {
        abort_unless(session('rol') === 'ADMINISTRADOR', 403);

        $request->validate([
            'nombre'     => 'required|max:100',
            'usuario'    => 'required|max:50|unique:usuarios,usuario,' . $usuario->id_usuario . ',id_usuario',
            'contrasena' => 'nullable|min:6|max:255',
            'id_rol'     => 'required|exists:roles,id_rol',
            'activo'     => 'required|boolean',
            'bloqueado'  => 'required|boolean',
        ]);

        $usuario->nombre    = $request->nombre;
        $usuario->usuario   = $request->usuario;
        $usuario->id_rol    = $request->id_rol;
        $usuario->activo    = $request->activo;
        $usuario->bloqueado = $request->bloqueado;

        // Si se desbloquea, se reinicia el contador de intentos fallidos
        if (! $usuario->bloqueado) {
            $usuario->intentos_fallidos = 0;
        }

        // La contraseña solo cambia si se escribió una nueva
        if ($request->filled('contrasena')) {
            $usuario->contrasena_hash = Hash::make($request->contrasena);
        }

        $usuario->save();

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Desactivar el usuario. No se borra de la base de datos (RF-11).
     */
    public function destroy(Usuario $usuario)
    {
        abort_unless(session('rol') === 'ADMINISTRADOR', 403);

        if ($usuario->id_usuario == session('id_usuario')) {
            return redirect()->route('usuarios.index')->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->activo = false;
        $usuario->save();

        return redirect()->route('usuarios.index')->with('success', 'Usuario desactivado. Ya no podrá iniciar sesión.');
    }
}
