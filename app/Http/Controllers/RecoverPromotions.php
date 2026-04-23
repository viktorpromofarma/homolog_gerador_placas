<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use App\Models\TypePromotions;


class RecoverPromotions extends Controller
{

    public function __invoke()
    {

       $promotions = $this->getPromotions(); 


        return response()->json([
            'status' => 'success',
            'result'   => $promotions
        ]);
    }


    public function getPromotions()
    {

        $promotions = TypePromotions::getAllPromotions();

        return $promotions;

    
    }
}