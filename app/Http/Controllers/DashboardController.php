<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Send each user to the dashboard for their role.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        return to_route($request->user()->role->dashboardRoute());
    }
}
