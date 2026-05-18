<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'telefone',
        'cpf',
        'data_nascimento',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'cpf', 
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'cpf' => 'encrypted', // CRIPTOGRAFIA AUTOMÁTICA
            'data_nascimento' => 'date',
        ];
    }
}