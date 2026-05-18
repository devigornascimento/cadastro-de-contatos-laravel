<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): Response
    {
        // 1. Mensagens de erro personalizadas conforme os seus critérios
        $mensagens = [
            'name.regex' => 'O nome não pode conter números ou caracteres especiais.',
            'name.max' => 'O nome deve ter no máximo 50 caracteres.',
            'email.email' => 'O e-mail deve conter o caractere "@" e ser válido.',
            'email.ends_with' => 'O e-mail deve terminar com ".com".',
            'email.max' => 'O e-mail deve ter no máximo 30 caracteres.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.max' => 'A senha deve ter no máximo 30 caracteres.',
            'cpf.regex' => 'O CPF deve conter apenas números, pontos e traços.',
            'cpf.min' => 'O CPF deve ter no mínimo 11 caracteres.',
            'cpf.max' => 'O CPF deve ter no máximo 15 caracteres.',
            'telefone.digits' => 'O telefone deve conter exatamente 11 números, sem letras ou caracteres especiais.',
            'data_nascimento.date_format' => 'A data de nascimento deve estar no formato dia/mês/ano e ser uma data válida no calendário.',
            'data_nascimento.before_or_equal' => 'O ano de nascimento não pode ser superior ao ano atual.',
        ];

        // 2. Regras de Validação Estritas
        $request->validate([
            // Aceita apenas letras (incluindo acentos) e espaços
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-ZÀ-ÿ\s]+$/'],

            // Exige formato de email e terminação específica
            'email' => ['required', 'string', 'email', 'max:30', 'ends_with:.com', 'unique:' . User::class],

            // Exige mínimo 8, máximo 30, letras maiúsculas/minúsculas e símbolos especiais
            'password' => ['required', 'confirmed', 'min:8', 'max:30', Rules\Password::min(8)->mixedCase()->symbols()],

            // Exige apenas números, pontos e traços, respeitando o tamanho
            'cpf' => ['required', 'string', 'min:11', 'max:15', 'regex:/^[0-9.\-]+$/'],

            // digits:11 já força que sejam EXATAMENTE 11 caracteres e APENAS números
            'telefone' => ['nullable', 'numeric', 'digits:11'],

            // Força o padrão brasileiro e impede datas futuras (ex: ano > 2026)
            'data_nascimento' => ['nullable', 'date_format:d/m/Y', 'before_or_equal:today'],
        ], $mensagens);

        // 3. Transformação e Limpeza de Dados (Pós-Validação)

        // Converte o nome para MAIÚSCULO
        $nomeFormatado = strtoupper($request->name);

        // Remove tudo que não for número (0-9) do CPF
        $cpfLimpo = preg_replace('/[^0-9]/', '', $request->cpf);

        // Converte a data do formato BR (dd/mm/yyyy) para o formato do Banco (yyyy-mm-dd)
        $dataNascimentoConvertida = null;
        if ($request->data_nascimento) {
            $dataNascimentoConvertida = \Carbon\Carbon::createFromFormat('d/m/Y', $request->data_nascimento)->format('Y-m-d');
        }

        // 4. Salvamento no Banco de Dados
        $user = User::create([
            'name' => $nomeFormatado,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
            'cpf' => $cpfLimpo,
            'telefone' => $request->telefone,
            'data_nascimento' => $dataNascimentoConvertida,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}