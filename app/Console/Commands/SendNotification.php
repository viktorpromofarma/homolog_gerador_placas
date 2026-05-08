<?php 


namespace App\Console\Commands;
use Illuminate\Support\Facades\Http;


Class SendNotification {

    public function Notification($dados)  {

    $response = Http::post('http://notificacao.promofarma.int/api/v1/notifications',$dados);


    return $response;

        
    }



}