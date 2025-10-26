<?php

namespace App\Observers;

use App\Models\Company;
use Illuminate\Support\Str; // Import the Str helper

class CompanyObserver
{
    /**
     * Handle the Company "creating" event.
     * This method is called automatically *before* a new company record is saved to the database.
     */
    public function creating(Company $company): void
    {
        // Only generate a code if one wasn't explicitly provided
        if (empty($company->code)) {
            $company->code = $this->generateUniqueCode();
        }
    }

    /**
     * Generate a unique, human-readable 8-character code.
     * Ensures the generated code doesn't already exist in the database.
     */
    private function generateUniqueCode(): string
    {
        $attempt = 0;
        do {
            // Generate an 8-character uppercase alphanumeric code
            $code = strtoupper(Str::random(8));
            // Basic replacement of potentially ambiguous characters (O/0, I/1)
            $code = str_replace(['O', '0', 'I', '1'], ['A', 'B', 'C', 'D'], $code);

            // Check if this code already exists in the companies table
            $exists = Company::where('code', $code)->exists();

            $attempt++;
            // Safety break to prevent infinite loop in extreme edge cases
            if ($attempt > 10) {
                throw new \Exception("Failed to generate a unique company code after multiple attempts.");
            }

        } while ($exists); // Keep trying until a unique code is found

        return $code;
    }

     /**
     * Handle the Company "created" event.
     */
    public function created(Company $company): void
    {
        // Placeholder for potential future logic after a company is created
    }

    /**
     * Handle the Company "updating" event.
     */
    public function updating(Company $company): void
    {
         // Prevent the unique code from being changed after creation
         if ($company->isDirty('code')) {
             $company->code = $company->getOriginal('code');
             // Optionally log a warning or throw an exception if code change is attempted
         }
    }

    // ... other event methods like updated, deleting, deleted, etc. ...
}