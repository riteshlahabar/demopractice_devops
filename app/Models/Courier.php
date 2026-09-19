<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    protected $fillable = [
        'courier_code',
        'name',
        'mobile',
        'alternate_mobile',
        'email',
        'company_name',
        'vehicle_type',
        'vehicle_number',
        'license_number',
        'address',
        'city',
        'pincode',
        'service_area',
        'id_proof_type',
        'id_proof_number',
        'joining_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return ['joining_date' => 'date'];
    }
}
