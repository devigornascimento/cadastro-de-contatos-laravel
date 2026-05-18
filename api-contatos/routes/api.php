<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\ContactController;

// ROTA DE LOGIN (Gera o Token de Acesso)
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Credenciais incorretas'], 401);
    }

    // Apaga tokens antigos e cria um novo
    $user->tokens()->delete();
    $token = $user->createToken('AcessoAPI')->plainTextToken;

    return response()->json(['token' => $token, 'message' => 'Login realizado com sucesso!']);
});


// ROTAS PROTEGIDAS (Exigem o Token)
Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rota de contatos
    Route::apiResource('contacts', ContactController::class);

});