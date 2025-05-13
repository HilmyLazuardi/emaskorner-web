<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class admin_group_branch extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_group_branches';

    public function __construct()
    {
        parent::__construct();

        $this->setTable(env('PREFIX_TABLE') . $this->table);
    }
}
