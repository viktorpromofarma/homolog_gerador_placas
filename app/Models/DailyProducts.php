<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Logs;

class DailyProducts extends Model

{
    protected $connection  = 'sqlsrv_secondary';

    protected $table = 'ETIQUETA_PLACAS_RESULTADO';

    protected $primaryKey = 'ID';

    public $timestamps = false; 


 


    public static function getDailyProducts()
    {

        $logs = Logs::all();

        $logs = json_decode(json_encode($logs), true);


        $ids = collect($logs)
        ->flatMap(fn($log) => preg_match('/[\["](?:IDs:|ids"\s*:\s*")[^\d]*([\d,\s]+)/', $log['COMANDO_EXECUTADO'], $m) ? array_map('trim', explode(',', $m[1])) : [])
        ->filter()
        ->unique()
        ->values();


        $products = DailyProducts::query()
        ->whereNotNull('ID_TEMPLATE')
        ->whereNotNull('LOJA')
        ->where('data_inicial', '<=', now()->format('Y-m-d'))
        ->where('data_final', '>=', now()->format('Y-m-d'))
        ->whereNotin('ID', $ids)
        ->get();


        return $products;
    }

}
