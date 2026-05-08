<?php 

namespace App\Models;

use App\Models\Logs;
use Illuminate\Database\Eloquent\Model;

class DailyProducts extends Model

{
    protected $connection  = 'sqlsrv';

    protected $table = 'ETIQUETA_PLACAS_RESULTADO';

    protected $primaryKey = 'ID';

    public $timestamps = false; 


    public static function getDailyProducts($loja)
    {

        $logs = Logs::all();

        $logs = json_decode(json_encode($logs), true);
        $ids = collect($logs)
        ->flatMap(fn($log) => preg_match('/[\["](?:IDS:|IDS"\s*:\s*")[^\d]*([\d,\s]+)/', $log['COMANDO_EXECUTADO'], $m) ? array_map('trim', explode(',', $m[1])) : [])
        ->filter()
        ->unique()
        ->values();

        $products = DailyProducts::query()
        ->whereNotNull('ID_TEMPLATE')
        ->whereNotNull('LOJA')
        ->whereNotin('ID', $ids)
        ->where('loja', $loja)
       
       
        ->get();


        return $products;
    }

}
