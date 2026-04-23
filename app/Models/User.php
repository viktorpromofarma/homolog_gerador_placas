<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $connection  = 'sqlsrv_secondary';
    protected $table       = 'USERS_GERADOR_PLACAS';
    protected $primaryKey  = 'ID';

    protected $fillable = ['USERNAME', 'PASSWORD', 'CREATED_AT'];
    protected $hidden   = ['PASSWORD'];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'PASSWORD'   => 'hashed'
        ];
    }
}
