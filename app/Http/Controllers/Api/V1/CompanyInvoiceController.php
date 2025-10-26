<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf; // 
use Carbon\Carbon; // 

class CompanyInvoiceController extends Controller
{
    /**
     * Generate and download a PDF invoice for the company admin.
     *
     * This method is called by the:
     * GET /api/v1/company/invoice/download route
     */
    public function download(Request $request)
    {
        // 1. Get the authenticated Company Admin
        $companyAdmin = Auth::user();

        // 2. Get their company (using the relationship we just defined)
        $company = $companyAdmin->company;

        if (!$company) {
            return response()->json(['message' => 'You are not associated with a company.'], 403);
        }

        // 3. Get all employees for this company, including their membership plan
        //    We use 'with('membershipPlan')' to eager-load the data and avoid N+1 queries
        $employees = User::where('company_id', $company->id)
                         ->where('role', 'employee') // only include employees
                         ->with('membershipPlan')
                         ->get();

        // 4. Calculate the total cost
        $total = $employees->sum(function($employee) {
            // Use null-safe operator and null coalescing operator
            return $employee->membershipPlan?->price ?? 0;
        });

        // 5. Prepare the data to pass to the Blade view
        $data = [
            'company' => $company,
            'employees' => $employees,
            'total' => $total,
            'invoiceDate' => Carbon::now()->format('F j, Y'), // e.g., "October 26, 2025"
        ];

        // 6. Load the PDF view, pass the data, and stream the download
        // 'pdf.invoice' maps to /resources/views/pdf/invoice.blade.php
        $pdf = Pdf::loadView('pdf.invoice', $data);

        // 7. Return the PDF as a download to the user's browser
        $filename = 'invoice-' . $company->name . '-' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }
}