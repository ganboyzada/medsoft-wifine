<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->user()->organization;
        $search = trim((string) $request->query('q', ''));

        $guests = Guest::query()
            ->where('organization_id', $organization->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                });
            })
            ->latest('last_seen_at')
            ->paginate(20)
            ->withQueryString();

        return view('organization.guests.index', compact('organization', 'guests', 'search'));
    }

    public function show(Request $request, Guest $guest): View
    {
        $organization = $request->user()->organization;
        abort_if($guest->organization_id !== $organization->id, 404);

        $guest->load([
            'sessions' => fn ($query) => $query->latest()->limit(40),
            'responses' => fn ($query) => $query->with('answers.question')->latest()->limit(20),
        ]);

        return view('organization.guests.show', compact('organization', 'guest'));
    }
}
