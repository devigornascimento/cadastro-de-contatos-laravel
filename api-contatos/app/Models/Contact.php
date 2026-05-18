<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    // Lista de campos que podem ser salvos no banco de dados
    protected $fillable = [
        'nome',
        'telefone',
        'user_id',
    ];
}