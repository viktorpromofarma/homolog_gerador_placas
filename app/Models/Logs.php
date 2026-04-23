<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    protected $connection  = 'sqlsrv_secondary';

    protected $table = 'LOG_IMPRESSOES_PLACAS';

    protected $primaryKey = 'id';

    protected $fillable = [
        'DATA_EXECUCAO',
        'COMANDO_EXECUTADO'
    ];

    public $timestamps = false;
}