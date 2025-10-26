<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Venue extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'address',
        'city',
        'state',
        'country',
        'capacity',
        'contact_email',
        'contact_phone',
        'is_verified',
        'is_available',
    ];
}
