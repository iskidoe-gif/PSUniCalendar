<?php

namespace App\Http\Controllers;

use App\Models\EventRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventRequestController extends Controller
{
    public function create()
    {
        return view('event-requests.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'venue_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after_or_equal:start_datetime'],
            'digital_documents' => ['required', 'array', 'min:1'],
            'digital_documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ];

        if ($user) {
            $rules['name'] = ['nullable', 'string', 'max:255'];
            $rules['email'] = ['nullable', 'email', 'max:255'];
        } else {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'email', 'max:255'];
        }

        $validated = $request->validate($rules);

        $name = $validated['name'] ?? $user->name ?? null;
        $email = $validated['email'] ?? $user->email ?? null;

        $files = [];
        foreach ($request->file('digital_documents', []) as $file) {
            $files[] = $file->store('event-documents', 'public');
        }

        EventRequest::create([
            'name' => $name,
            'email' => $email,
            'title' => $validated['title'],
            'venue_name' => $validated['venue_name'],
            'description' => $validated['description'] ?? '',
            'start_datetime' => $validated['start_datetime'],
            'end_datetime' => $validated['end_datetime'],
            'digital_documents' => $files,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Your event request has been submitted with required digital documents.');
    }
}
