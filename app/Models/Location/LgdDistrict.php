<?php

namespace App\Models\Location;

use Illuminate\Database\Eloquent\Model;

class LgdDistrict extends Model
{
    protected $table = 'lgd_districts';

    protected $primaryKey = 'district_code';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}
