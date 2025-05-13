<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class error_log extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'error_logs';

    public function __construct()
    {
        parent::__construct();

        $this->setTable(env('PREFIX_TABLE') . $this->table);
    }
}
