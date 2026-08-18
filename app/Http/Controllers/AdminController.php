<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventRequest;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        $events = EventRequest::where('status', 'approved')
            ->orderBy('start_datetime', 'asc')
            ->get()
            ->map(function ($event) {
                return [
                    'title' => $event->title,
                    'start' => $event->start_datetime,
                    'end' => $event->end_datetime,
                    'description' => $event->description,
                    'venue' => $event->venue_name,
                    'campus' => $this->resolveCampus($event),
                ];
            });

        $adminRequests = [];
        if (Auth::check()) {
            $adminRequests = EventRequest::where('email', Auth::user()->email)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $totalEvents = EventRequest::where('status', 'approved')->count();
        $upcomingEvents = EventRequest::where('status', 'approved')
            ->where('start_datetime', '>=', now())
            ->count();
        $accountBadge = $this->getAccountBadge(Auth::user());

        return view('admin.dashboard', compact('events', 'adminRequests', 'totalEvents', 'upcomingEvents', 'accountBadge'));
    }

    public function venues()
    {
        $venues = EventRequest::where('status', 'approved')
            ->selectRaw('venue_name, COUNT(*) as events_count')
            ->groupBy('venue_name')
            ->orderBy('venue_name')
            ->get();

        return view('admin.manage-venues', compact('venues'));
    }

    public function calendar()
    {
        $events = EventRequest::where('status', 'approved')
            ->orderBy('start_datetime', 'asc')
            ->get()
            ->map(function ($event) {
                return [
                    'title' => $event->title,
                    'start' => $event->start_datetime,
                    'end' => $event->end_datetime,
                    'description' => $event->description,
                    'venue' => $event->venue_name,
                    'campus' => $this->resolveCampus($event),
                ];
            });

        $totalEvents = EventRequest::where('status', 'approved')->count();
        $upcomingEvents = EventRequest::where('status', 'approved')
            ->where('start_datetime', '>=', now())
            ->count();
        $accountBadge = $this->getAccountBadge(Auth::user());

        return view('admin.calendar-only', compact('events', 'totalEvents', 'upcomingEvents', 'accountBadge'));
    }

    private function resolveCampus($event): string
    {
        if (!empty($event->campus)) {
            return $event->campus;
        }

        $venue = strtolower($event->venue_name ?? '');

        if (str_contains($venue, 'alaminos')) {
            return 'Alaminos Campus';
        }

        if (str_contains($venue, 'lingayen')) {
            return 'Lingayen Campus';
        }

        if (str_contains($venue, 'binmaley')) {
            return 'Binmaley Campus';
        }

        return 'All Campus';
    }

    private function getAccountBadge($user): array
    {
        $label = 'ADMIN';
        $style = 'bg-slate-100 text-slate-800';
        $subtitle = 'General admin account';

        if ($user) {
            $lowerEmail = strtolower($user->email ?? '');
            $lowerName = strtolower($user->name ?? '');

            if (str_contains($lowerEmail, 'alaminos')) {
                $label = 'ALAMINOS';
                $style = 'bg-emerald-100 text-emerald-700';
                $subtitle = 'Alaminos Campus admin';
            } elseif (str_contains($lowerEmail, 'lingayen')) {
                $label = 'LINGAYEN';
                $style = 'bg-indigo-100 text-indigo-700';
                $subtitle = 'Lingayen Campus admin';
            } elseif (str_contains($lowerEmail, 'binmaley')) {
                $label = 'BINMALEY';
                $style = 'bg-amber-100 text-amber-700';
                $subtitle = 'Binmaley Campus admin';
            } elseif (str_contains($lowerEmail, 'ccs') || str_contains($lowerName, 'ccs')) {
                $label = 'CCS';
                $style = 'bg-cyan-100 text-cyan-700';
                $subtitle = 'College of Computer Science';
            } elseif (str_contains($lowerEmail, 'psu') || str_contains($lowerName, 'psu')) {
                $label = 'PSU';
                $style = 'bg-indigo-100 text-indigo-700';
                $subtitle = 'PSU admin account';
            }
        }

        return compact('label', 'style', 'subtitle');
    }

    public function requestVenue(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'venue_name' => 'required|string|max:255',
            'campus' => 'required|string|max:255',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after_or_equal:start_datetime',
            'description' => 'nullable|string',
            'digital_documents' => ['nullable', 'array'],
            'digital_documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ]);

        $files = [];
        foreach ($request->file('digital_documents', []) as $file) {
            $files[] = $file->store('event-documents', 'public');
        }

        $user = Auth::user();

        EventRequest::create([
            'name' => $user->name,
            'email' => $user->email,
            'title' => $request->input('title'),
            'venue_name' => $request->input('venue_name'),
            'campus' => $request->input('campus'),
            'description' => $request->input('description', ''),
            'start_datetime' => $request->input('start_datetime'),
            'end_datetime' => $request->input('end_datetime'),
            'status' => 'pending',
            'digital_documents' => $files,
        ]);

        return back()->with('success', 'Venue request submitted successfully and is pending approval.');
    }
}
