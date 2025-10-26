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
        Schema::table('companies', function (Blueprint $table) {
            // Add a unique code column after 'name'
            $table->string('code')->unique()->nullable()->after('name');

            // Add a foreign key to link to a default membership plan
            $table->foreignId('membership_plan_id')
                  ->nullable() // A company might not have a default plan
                  ->after('code')
                  ->constrained('membership_plans') // Ensure it links to the membership_plans table
                  ->nullOnDelete(); // If the plan is deleted, set this company's default to null

            // Add a status column
            $table->enum('status', ['active', 'suspended'])->default('active')->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Drop the foreign key constraint *before* dropping the column
            $table->dropForeign(['membership_plan_id']);
            // Drop the columns we added
            $table->dropColumn(['code', 'membership_plan_id', 'status']);
        });
    }
};