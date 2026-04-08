<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RhidComplianceController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->company()->firstOrFail();

        return Inertia::render('Client/Rhid/Compliance', [
            'configured' => $company->rhidConfigured(),
        ]);
    }
}
