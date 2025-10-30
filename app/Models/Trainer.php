<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{
    /** @use HasFactory<\Database\Factories\TrainerFactory> */
    use HasFactory;
    public function partner() { return $this->belongsTo(Partner::class); }
    public function classes() { return $this->belongsToMany(ClassModel::class, 'class_trainer'); }
}
