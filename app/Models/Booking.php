<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['start_time', 'end_time'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
