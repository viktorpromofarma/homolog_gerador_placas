<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
        protected $connection  = 'sqlsrv_secondary';

        protected $table = 'PRODUTOS';

        protected $primaryKey = 'PRODUTO';


        public $timestamps = false;
}
