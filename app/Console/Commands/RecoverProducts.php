<?php 

namespace App\Console\Commands;


use App\Models\DailyProducts;
use App\Models\Logs;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Http\Controllers\GenerateImage;

class RecoverProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:recover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
public function handle()
{
    $products = $this->getAllDailyProducts();

    if ($products->isEmpty()) {
        $this->error('Nenhum produto encontrado');
        return;
    }

    $grouped = $products->groupBy(function ($product) {
        return $product->ID_TEMPLATE . '_' . $product->LOJA;
    });
    
    foreach ($grouped as $group) {
        $first = $group->first();
  
        $payload = $group->map(function ($product) {
            return [
                "product"             => $product->produto,
                "promotion"           => $product->procfit_tipo_id,
                "description"         => $product->descricao_reduzida,
                "ean"                 => "ean: " . $product->ean,
                "max_price"           => $product->preco_maximo,
                "sail_price"          => $product->preco_venda,
                "promotion_price"     => $product->preco_promocao,
                "percentage_discount" => $product->subtitulo_1,
                "initial_date"        => $product->data_inicial,
                "final_date"          => $product->data_final,
                "buy"                 => $product->leve,
                "get"                 => $product->pague,
                "promotion_title"     => $product->titulo_etiqueta,
                "expiration_date"     => $product->validade,
                "X"                   => $product->leve,
                "Y"                   => $product->pague,
            ];
        })->values()->toArray();

     


        try {
             $request = new Request([
            'template_id'     => $first->ID_TEMPLATE,
            'store'           => $first->LOJA,
            'impression_date' => "Impresso em: " . now()->format('d/m/Y'),
            'payload'         => $payload,
        ]);

        $response     = app(GenerateImage::class)($request);
        $responseData = json_decode($response->getContent(), true);

        if ($responseData['status'] === 'success') {
              $ids = $group->pluck('id')->implode(', ');
              Logs::create([
                'DATA_EXECUCAO'     => now()->format('d-m-Y'),
                'COMANDO_EXECUTADO' => json_encode(
                    array_merge(['ids' => $ids], $request->all()),JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
                ),
            ]);
        }
        } catch (\Throwable $th) {
           return $this->info($th->getMessage());
        }

      
    }
}

    public function getAllDailyProducts()
    {
        $products = DailyProducts::getDailyProducts();


        return $products;
    }
}