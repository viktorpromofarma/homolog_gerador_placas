<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestGeneratorImage extends Model
{
    protected $connection  = 'sqlsrv_secondary';

    protected $table = 'REQUISICOES_GERADOR_PLACAS';

    protected $primaryKey = 'REQUISICAO_GERADOR_PLACAS';



    protected $fillable = [
        'TEMPLATE_ID',
        'REQUISICAO',
        'DATA_REQUISICAO',
        'HORA_REQUISICAO',
        'LOJA',
        'PATH_PDF'
    ];


    

    public $timestamps = false;

  
}