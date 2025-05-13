<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class shopping_cart extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $primaryKey = 'buyer_id';
    protected $table = 'shopping_cart';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'buyer_id', 'product_item_variant_id', 'qty'
    ];
}