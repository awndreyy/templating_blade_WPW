
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_id',
        'product_name',
        'gross_amount',
        'status',
        'snap_token',
    ];
}
