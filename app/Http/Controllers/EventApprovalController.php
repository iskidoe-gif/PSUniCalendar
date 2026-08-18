<?php

namespace App\Http\Controllers;

use App\Models\EventRequest; // Adjust to match your model name
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Illuminate\Http\Request;

class EventApprovalController extends Controller
{
    public function approve($id)
    {
        $booking = EventRequest::findOrFail($id);

        try {
            if (config('services.google.service_account_json') && config('services.google.calendar_id')) {
                $client = new Client();
                $client->setAuthConfig(config('services.google.service_account_json'));
                $client->addScope(Calendar::CALENDAR);

                $service = new Calendar($client);

                $event = new Event([
                    'summary' => $booking->title,
                    'location' => $booking->venue_name,
                    'description' => $booking->description,
                    'start' => [
                        'dateTime' => date('c', strtotime($booking->start_datetime)),
                        'timeZone' => 'Asia/Manila',
                    ],
                    'end' => [
                        'dateTime' => date('c', strtotime($booking->end_datetime)),
                        'timeZone' => 'Asia/Manila',
                    ],
                ]);

                $googleEvent = $service->events->insert(config('services.google.calendar_id'), $event);

                $booking->update([
                    'status' => 'approved',
                    'google_event_id' => $googleEvent->getId(),
                ]);

                return back()->with('success', 'Event approved and added to Google Calendar.');
            }

            $booking->update(['status' => 'approved']);

            return back()->with('success', 'Event approved successfully.');
        } catch (\Throwable $e) {
            $booking->update(['status' => 'approved']);

            return back()->with('error', 'Event approved locally, but Google Calendar sync could not be completed.');
        }
    }

    public function reject($id)
    {
        $booking = EventRequest::findOrFail($id);
        $booking->update(['status' => 'rejected']);

        return back()->with('success', 'Event request rejected.');
    }
}