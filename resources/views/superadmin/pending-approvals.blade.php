<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approvals - UniCalendar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen">
        <main class="overflow-y-auto p-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Pending Approvals</h1>
                <p class="text-sm text-gray-500 mt-1">Review and approve or reject venue requests.</p>
            </div>

            @include('partials.calendar-navbar')

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 text-xs text-gray-400 uppercase">
                                <th class="py-3 px-4">Event Title</th>
                                <th class="py-3 px-4">Requested By</th>
                                <th class="py-3 px-4">Campus</th>
                                <th class="py-3 px-4">Venue</th>
                                <th class="py-3 px-4">Date & Time</th>
                                <th class="py-3 px-4">Documents</th>
                                <th class="py-3 px-4">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse($requests as $request)
                                <tr>
                                    <td class="py-3 px-4 font-semibold text-gray-800">{{ $request->title }}</td>
                                    <td class="py-3 px-4">{{ $request->name }}<br><span class="text-xs text-slate-500">{{ $request->email }}</span></td>
                                    <td class="py-3 px-4">{{ $request->campus ?? 'Not specified' }}</td>
                                    <td class="py-3 px-4">{{ $request->venue_name }}</td>
                                    <td class="py-3 px-4">{{ date('M d, Y', strtotime($request->start_datetime)) }}<br><span class="text-xs text-slate-500">{{ date('g:i A', strtotime($request->start_datetime)) }} - {{ date('g:i A', strtotime($request->end_datetime)) }}</span></td>
                                    <td class="py-3 px-4">
                                        @if(!empty($request->digital_documents))
                                            <div class="space-y-2">
                                                @foreach($request->digital_documents as $doc)
                                                    <a href="{{ asset('storage/' . $doc) }}" target="_blank" class="block text-xs text-indigo-600 hover:underline">Document {{ $loop->iteration }}</a>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-500">None uploaded</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 space-x-2">
                                        <form method="POST" action="{{ route('superadmin.approve', ['id' => $request->id]) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('superadmin.reject', ['id' => $request->id]) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-bold">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 px-4 text-center text-sm text-slate-500">No pending venue requests at this time.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
