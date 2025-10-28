<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Add this line if you want soft deletes

class Partner extends Model
{
    use HasFactory; // Your factory exists, so this is correct
    use SoftDeletes; // Add this line if you want soft deletes, as your migration has the column

    /**
     * The attributes that are mass assignable.
     * This array matches your migration file exactly.
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'city',
        'type',
        'description',
        'status',
        'latitude',
        'longitude',
        'phone_number',
        'cover_image_url',
    ];

    /**
     * Get the classes associated with the partner.
     * (Make sure you have the ClassModel imported or use ::class)
     */
    public function classes()
    {
        return $this->hasMany(\App\Models\ClassModel::class); // Adjusted to use full namespace
    }

    // Add any other relationships here (e.g., trainers)
}
