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
    Schema::create('membership_plans', function (Blueprint $table) {
        $table->id();
        $table->string('name');                 // plan name
        $table->decimal('price', 8, 2);         // plan price
        $table->text('features');               // features of the plan
        $table->enum('status', ['active', 'inactive'])->default('active'); // status
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_plans');
    }
};
