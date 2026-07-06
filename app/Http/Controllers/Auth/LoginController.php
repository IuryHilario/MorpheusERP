<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Authenticate the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'senha' => 'required|string',
        ]);

        // Buscar o usuário pelo nome de usuário OU email
        $user = DB::table('usuarios')
            ->where('nome_Usuario', $credentials['login'])
            ->orWhere('email', $credentials['login'])
            ->first();

        if (!$user || !Hash::check($credentials['senha'], $user->senha)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login ou senha inválidos'
            ]);
        }

        // Criar um objeto User para autenticação do Laravel
        $userModel = User::where('id_Usuario', $user->id_Usuario)->first();

        // Se o usuário existir no modelo, faz o login via Auth
        if ($userModel) {
            Auth::login($userModel);
        } else {
            Log::warning('Não foi possível fazer login via Auth, apenas via sessão');
        }

        // Armazenar informações do usuário na sessão (como backup)
        session([
            'logged_in' => true,
            'user_id' => $user->id_Usuario,
            'user_type' => $user->tipo_Usuario,
            'user_name' => $user->nome_Usuario
        ]);

        return response()->json(['status' => 'success']);
    }
    
    /**
     * Log the user out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        session()->flush();
        
        return redirect()->route('auth.login');
    }
}