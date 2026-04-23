<?php

namespace App\Rules;

use Closure;
use Illuminate\Container\Attributes\DB;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use App\Models\Products;

class ProductExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
            if(!Products::where('PRODUTO', $value)->exists()) {
                $fail('O produto não existe.');
            }
    }

}
