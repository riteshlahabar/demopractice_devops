<?php

namespace App\Models\Location;

use Illuminate\Database\Eloquent\Model;

class LgdLocalBody extends Model
{
    protected $table = 'lgd_local_bodies';

    protected $primaryKey = 'sr_no';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}
