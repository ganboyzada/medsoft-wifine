<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.organizations.index');
        }

        return redirect()->route('organization.dashboard');
    }
}
