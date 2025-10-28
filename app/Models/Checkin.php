<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkin extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * We only need user_id and partner_id, as checkin_time defaults to now.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'partner_id',
        'checkin_time', // Include if you want to set it manually sometimes
    ];

    /**
     * The attributes that should be cast.
     * Ensure checkin_time is treated as a date/time.
     *
     * @var array
     */
    protected $casts = [
        'checkin_time' => 'datetime',
    ];


    /**
     * Get the user that owns the check-in.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the partner location for the check-in.
     */
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}