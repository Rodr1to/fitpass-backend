<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    /** @use HasFactory<\Database\Factories\ClassModelFactory> */
    use HasFactory;
    public function partner() { return $this->belongsTo(Partner::class); }
    public function trainers() { return $this->belongsToMany(Trainer::class, 'class_trainer'); }
    public function bookings() { return $this->hasMany(Booking::class); }
}
