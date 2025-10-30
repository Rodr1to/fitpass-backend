<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('checkins', function (Blueprint $table) {
            $table->id(); // Primary key

            // Foreign key linking to the user who checked in
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Foreign key linking to the partner location
            $table->foreignId('partner_id')->constrained('partners')->onDelete('cascade');

            // Timestamp for when the check-in occurred
            // Defaulting to the current time when the record is created
            $table->timestamp('checkin_time')->useCurrent();

            // Optional: Add any other relevant info, e.g., status, notes
            // $table->string('status')->default('completed');

            $table->timestamps(); // Adds created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkins');
    }
};
