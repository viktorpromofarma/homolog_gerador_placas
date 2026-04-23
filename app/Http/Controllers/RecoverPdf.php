<?php 


namespace App\Http\Controllers;

use App\Models\RequestGeneratorImage;
use App\Models\RequestGeneratorImageProduct;
use App\Services\PdfService;
use App\Services\TemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class RecoverPdf extends Controller
{

    public function __invoke(Request $request)
    {
        $pdfPath = $this->recoverPdf($request);
        return response()->json([
            'status' => 'success',
            'result'   => $pdfPath
        ]);
    }

    public function recoverPdf(Request $request)
    {
            $pdfPath = RequestGeneratorImage::query()
                ->leftJoin('REQUISICOES_GERADOR_PLACAS_PRODUTOS', 
                    'REQUISICOES_GERADOR_PLACAS.REQUISICAO_GERADOR_PLACAS', 
                    '=', 'REQUISICOES_GERADOR_PLACAS_PRODUTOS.REQUISICAO_GERADOR_PLACAS'
                )
                ->where('REQUISICOES_GERADOR_PLACAS.LOJA', $request->store)
                ->where('REQUISICOES_GERADOR_PLACAS.DATA_REQUISICAO', $request->requisition_date)
                ->when($request->filled('product'), function($query) use ($request) {
                    $query->where('REQUISICOES_GERADOR_PLACAS_PRODUTOS.PRODUTO', $request->product);
                })
                ->when($request->filled('promotion'), function($query) use ($request) {
                    $query->where('REQUISICOES_GERADOR_PLACAS_PRODUTOS.PROMOCAO', $request->promotion);
                })
                ->get();

            return $pdfPath;
    }



  
}