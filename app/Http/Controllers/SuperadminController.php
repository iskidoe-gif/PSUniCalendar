<?php

namespace App\Http\Controllers;

use App\Models\EventRequest;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperadminController extends Controller
{
    public function dashboard()
    {
        $requests = EventRequest::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $approvedEvents = EventRequest::where('status', 'approved')
            ->orderBy('start_datetime', 'asc')
            ->get();

        $events = $approvedEvents->map(function ($event) {
            return [
                'title' => $event->title,
                'start' => $event->start_datetime,
                'end' => $event->end_datetime,
                'description' => $event->description,
                'venue' => $event->venue_name,
                'campus' => $this->resolveCampus($event),
            ];
        });

        $campusEventCounts = collect(['Alaminos Campus', 'Lingayen Campus', 'Binmaley Campus'])
            ->mapWithKeys(function ($campus) use ($approvedEvents) {
                return [$campus => $approvedEvents->filter(fn ($event) => $this->resolveCampus($event) === $campus)->count()];
            });

        $universityWideEvents = $approvedEvents->filter(fn ($event) => $this->resolveCampus($event) === 'All Campus')->count();
        $upcomingEvents = $approvedEvents->filter(function ($event) {
            return !empty($event->start_datetime) && $event->start_datetime >= now();
        })->count();

        return view('superadmin.dashboard', compact(
            'requests',
            'events',
            'campusEventCounts',
            'universityWideEvents',
            'upcomingEvents'
        ));
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

    public function pendingApprovals()
    {
        $requests = EventRequest::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('superadmin.pending-approvals', compact('requests'));
    }

    public function manageVenues()
    {
        $venues = Venue::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(function ($venue) {
                $venue->venue_name = $venue->name;
                $venue->events_count = EventRequest::where('status', 'approved')
                    ->where('venue_name', $venue->name)
                    ->count();

                return $venue;
            });

        if ($venues->isEmpty()) {
            $venues = collect([
                ['id' => 1, 'name' => 'Main Auditorium', 'venue_name' => 'Main Auditorium', 'events_count' => 2],
                ['id' => 2, 'name' => 'Science Hall', 'venue_name' => 'Science Hall', 'events_count' => 1],
                ['id' => 3, 'name' => 'Student Center', 'venue_name' => 'Student Center', 'events_count' => 3],
                ['id' => 4, 'name' => 'Sports Gym', 'venue_name' => 'Sports Gym', 'events_count' => 1],
            ])->map(function ($venue) {
                return (object) $venue;
            });
        }

        return view('superadmin.manage-venues', compact('venues'));
    }

    public function storeVenue(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:venues,name'],
        ]);

        Venue::create(['name' => trim($request->name)]);

        return redirect()->route('superadmin.venues')->with('success', 'Venue added successfully.');
    }

    public function updateVenue(Request $request, Venue $venue)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:venues,name,' . $venue->id],
        ]);

        $venue->update(['name' => trim($request->name)]);

        return redirect()->route('superadmin.venues')->with('success', 'Venue updated successfully.');
    }

    public function destroyVenue(Venue $venue)
    {
        $venue->delete();

        return redirect()->route('superadmin.venues')->with('success', 'Venue removed successfully.');
    }

    public function venueEvents($venue)
    {
        $events = EventRequest::where('status', 'approved')
            ->where('venue_name', $venue)
            ->orderBy('start_datetime', 'asc')
            ->get();

        return view('superadmin.venue-events', compact('events', 'venue'));
    }
}

