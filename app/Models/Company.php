<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo
use Illuminate\Database\Eloquent\Relations\HasMany;   // Import HasMany

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'contact_email',
        'contact_phone',
        'address',
        'code',                 // 👈 Add this
        'membership_plan_id', // 👈 Add this
        'status',               // 👈 Add this
    ];

    /**
     * Get the users associated with the company.
     */
    public function users(): HasMany // Add return type hint
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the default membership plan associated with the company.
     */
    public function defaultPlan(): BelongsTo // Add return type hint and method
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }
}
