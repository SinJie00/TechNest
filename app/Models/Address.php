<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = ['user_id', 'line1', 'line2', 'city', 'state', 'postal_code', 'country'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
