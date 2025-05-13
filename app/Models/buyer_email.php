<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class buyer_email extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'buyer_emails';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'value', 'created_at', 'updated_at'
    ];
}