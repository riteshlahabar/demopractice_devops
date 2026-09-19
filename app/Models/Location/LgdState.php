<?php

namespace App\Models\Location;

use Illuminate\Database\Eloquent\Model;

class LgdState extends Model
{
    protected $table = 'lgd_states';

    protected $primaryKey = 'state_code';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}
