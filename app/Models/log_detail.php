<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class log_detail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'log_details';

    public function __construct()
    {
        parent::__construct();

        $this->setTable(env('PREFIX_TABLE') . $this->table);
    }
}
