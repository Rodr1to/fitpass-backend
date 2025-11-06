<?php

namespace App\Http\Controllers\Api\V1;

// 1. Import BaseApiController
use App\Http\Controllers\Api\V1\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use OpenApi\Annotations as OA;
use Throwable; // 2. Import Throwable

/**
 * @OA\Tag(
 * name="Company Admin - Invoicing",
 * description="Endpoints for company invoice management"
 * )
 */
class CompanyInvoiceController extends BaseApiController // 3. Extend BaseApiController
{
    /**
     * @OA\Get(
     * path="/api/v1/company/invoice/download",
     * summary="[Company Admin] Download a PDF invoice for the admin's company",
     * tags={"Company Admin - Invoicing"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="PDF invoice file.",
     * @OA\MediaType(
     * mediaType="application/pdf"
     * )
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=403, description="Forbidden (User is not a company admin or has no company)")
     * )
     */
    public function download(Request $request)
    {
        try { // 4. Add try...catch block
            $companyAdmin = Auth::user();
            $company = $companyAdmin->company;

            if (!$company) {
                return $this->sendError('You are not associated with a company.', [], 403);
            }

            $employees = User::where('company_id', $company->id)
                ->where('role', 'employee')
                ->with('membershipPlan')
                ->get();

            $total = $employees->sum(function($employee) {
                return $employee->membershipPlan?->price ?? 0;
            });

            $data = [
                'company' => $company,
                'employees' => $employees,
                'total' => $total,
                'invoiceDate' => Carbon::now()->format('F j, Y'),
            ];

            $pdf = Pdf::loadView('pdf.invoice', $data);

            $filename = 'invoice-' . $company->name . '-' . Carbon::now()->format('Y-m-d') . '.pdf';
            return $pdf->download($filename);

        } catch (Throwable $e) {
            // 5. Use handleException for errors
            return $this->handleException($e, 'Failed to generate invoice.');
        }
    }
}