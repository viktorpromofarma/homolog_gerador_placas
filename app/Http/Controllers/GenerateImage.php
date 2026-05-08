<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\RequestGeneratorImage;
use App\Models\RequestGeneratorImageProduct;
use App\Models\TypePromotions;
use App\Services\LabelService;
use App\Services\PdfService;
use App\Services\TemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GenerateImage extends Controller
{
    public function __invoke(Request $request)
    {
        $payloads = $request->payload;

      

        try {
             $request->validate([
            'payload' => ['required', 'array'],
         //   'payload.*.product' => ['required', new ProductExists],
        ]);
        } catch (\Throwable $th) {
              
                return response()->json([
                        'status'  => 'error',
                        'message' => 'Produto(s) inválidos',
                        'errors' => $th->getMessage()
                ],422);
        }

        $results  = [];
        $requisicaoId = (string) \Illuminate\Support\Str::uuid();
        foreach ($payloads as $payload) {
            $product = $this->store($request, $payload, $requisicaoId);
            if ($product instanceof JsonResponse) {
                return $product;
            }

            $imageResponse = Http::acceptJson()
                ->post(TemplateService::GENERATE_IMAGE_URL, [
                    'template_id'     => $request->template_id,
                    'store'           => $request->store,
                    'type'            => $request->type,  
                    'impression_date' => $request->impression_date,
                    'payload'         => [
                        'product'             => $payload['product'],
                        'quantity'            => $payload['quantity'],
                        'promotion'           => $payload['promotion'],
                        'description'         => $payload['description'],
                        'barcode'             => $payload['ean'],
                        'ean'                 => $payload['ean'],
                        'max_price'           => $payload['max_price'],
                        'sail_price'          => $payload['sail_price'],
                        'promotion_price'     => $payload['promotion_price'],
                        'percentage_discount' => $payload['percentage_discount'],
                        'initial_date'        => $payload['initial_date'],
                        'final_date'          => $payload['final_date'],
                        'buy'                 => $payload['buy'],
                        'get'                 => $payload['get'],
                        'promotion_title'     => $payload['promotion_title'],
                        'expiration_date'     => $payload['expiration_date'],
                        'X'                   => $payload['X'],
                        'Y'                   => $payload['Y'],
                    ],
                ]);
           
           

            if ($imageResponse->failed()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Falha ao gerar imagem',
                    'code'    => $imageResponse->status(),
                    'body'    => $imageResponse->body(),
                ], 500);
            }

            $base64Image = $imageResponse->json('image');
            $quantity    = max(1, (int) ($payload['quantity'] ?? 1)); 

          

            for ($i = 0; $i < $quantity; $i++) {
            $results[] = [
                'product'     => $payload['product'],
                'imageBase64' => $base64Image,
                'id'          => $product->REQUISICAO_GERADOR_PLACAS,
            ];
        }
   }
    
        $printResponse = $this->print(
            new Request([
                'id'          => $results[0]['id'],
                'type'        =>  $request->type,
                'imageBase64' => array_column($results, 'imageBase64'),
            ])
        );

        $printData = json_decode($printResponse->getContent(), true);

     
        RequestGeneratorImage::where('REQUISICAO_GERADOR_PLACAS', $results[0]['id'])
            ->update(['PATH_PDF' => public_path('img') . '/' . $printData['pdf']]);

        if ($printResponse->getStatusCode() !== 200) {
            return $printResponse;
        }

      

        return response()->json([
            'status'      => 'success',
            'template_id' => $request->template_id,
            'products'    => $results,
            'type'        => $request->type,
            'pdf'         => json_decode($printResponse->getContent(), true)['pdf'],
        ]);
    }

    public function print(Request $request): JsonResponse
    {
       
        if (!$request->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'O id é obrigatório',
            ], 422);
        }

        $products = RequestGeneratorImageProduct::where('REQUISICAO_GERADOR_PLACAS', $request->id)
            ->when($request->produto, fn($q) => $q->whereIn('PRODUTO', (array) $request->produto))
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nenhum produto encontrado',
            ], 404);
        }

        $images = $request->imageBase64;

      

    /**
     * type 1 = ETIQUETA
     * type 2 = PLACA
     * 
     * */

        if($request->type == 1){
            $pdfPath = (new LabelService())->generate($images, 'print_' . $request->id);
        } elseif($request->type == 2 ) {
            $pdfPath = (new PdfService())->generate($images, 'print_' . $request->id);
        }



        return response()->json([
            'status' => 'success',
            'pdf'    => $pdfPath
        ]);
    }

    

    public function store(Request $request, array $payload, string $requisicaoId): RequestGeneratorImageProduct|JsonResponse
    {

        try {
            $data = [
                'TEMPLATE_ID'     => $request->template_id,
                'LOJA'            => $request->store,
                'PRODUTO'         => $payload['product'],
                'PROMOCAO'       => $payload['promotion'],
                'DATA_REQUISICAO' => now()->format('d-m-Y'),
                'REQUISICAO'      => $requisicaoId
            ];

       

           if (!TypePromotions::isValid((int) $payload['promotion'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Promotion not found',
            ], 404);
        }

            $master = RequestGeneratorImage::firstOrCreate(
                [
                    'TEMPLATE_ID' => $data['TEMPLATE_ID'],
                    'LOJA'           => $data['LOJA'],
                    'DATA_REQUISICAO' => $data['DATA_REQUISICAO'],
                    'REQUISICAO'      => $data['REQUISICAO']
                 ],['HORA_REQUISICAO' => now()->format('H:i')]
            );

            return RequestGeneratorImageProduct::create([
                'REQUISICAO_GERADOR_PLACAS' => $master->getKey(),
                'PRODUTO'                   => $data['PRODUTO'],
                'LOJA'                      => $data['LOJA'],
                'PROMOCAO'                  => $data['PROMOCAO'],
            ]);


        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to store request: ' . $th->getMessage(),
            ]);
        }
    }
}
