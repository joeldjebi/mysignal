<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LandingPageContactController extends Controller
{
    public function index(): View
    {
        return view('super-admin.landing-page.contacts', [
            'submissions' => ContactSubmission::query()
                ->latest()
                ->paginate(20),
            'unreadCount' => ContactSubmission::query()
                ->whereNull('read_at')
                ->count(),
        ]);
    }

    public function markAsRead(ContactSubmission $contactSubmission): RedirectResponse
    {
        $contactSubmission->update(['read_at' => now()]);

        return back()->with('success', 'Le message a ete marque comme lu.');
    }
}
