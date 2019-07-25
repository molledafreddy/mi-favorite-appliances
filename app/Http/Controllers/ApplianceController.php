<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Appliance;

class ApplianceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $appliances = Appliance::search(request()->search)->get();

        return Response::json(['success' => true, 'data' => $appliances]);
    }
}
