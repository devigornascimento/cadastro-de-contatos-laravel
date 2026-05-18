<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    // RETORNA OS CONTATOS DO USUÁRIO LOGADO (GET)
    public function index(Request $request)
    {
        $contatos = Contact::where('user_id', $request->user()->id)->get();
        return response()->json($contatos);
    }

    // SALVA UM NOVO CONTATO COM TRATATIVA (POST)
    public function store(Request $request)
    {
        // 1. Validação
        $request->validate([
            'nome' => ['required', 'string', 'max:50', 'regex:/^[a-zA-ZÀ-ÿ\s]+$/'],
            'telefone' => ['required', 'string', 'min:11', 'max:11', 'regex:/^[0-9]+$/'],
        ], [
            'nome.regex' => 'O nome do contato não pode conter números ou caracteres especiais.',
            'nome.max' => 'O nome do contato deve ter no máximo 50 caracteres.',
            'telefone.regex' => 'O telefone deve conter apenas números.',
            'telefone.min' => 'O telefone deve ter exatamente 11 números.',
            'telefone.max' => 'O telefone deve ter exatamente 11 números.',
        ]);

        // 2. Tratativa dos Dados
        $telefoneLimpo = preg_replace('/[^0-9]/', '', $request->telefone);
        $nomeFormatado = strtoupper($request->nome);

        // 3. Salvamento
        $contato = Contact::create([
            'nome' => $nomeFormatado,
            'telefone' => $telefoneLimpo,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($contato, 201);
    }

    // EXCLUI UM CONTATO 
    public function destroy(Request $request, $id)
    {
        $contato = Contact::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$contato) {
            return response()->json(['message' => 'Não autorizado ou contato inexistente'], 403);
        }

        $contato->delete();
        return response()->json(['message' => 'Excluído com sucesso']);
    }
}