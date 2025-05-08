<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserList extends Model
{
    use HasFactory;

    protected $table = 'prayer_conference';

    protected $fillable = [
        'full_name',
        'kingschat_handle',
        'kc_user_id',
        'phone_number',
        'email',
        'email_active',
        'designation',
        'zone',
        'country',
        'subscribed_at'
    ];

    public function getFullNameAttribute($value)
    {
        return ucwords($value);
    }
    
}


