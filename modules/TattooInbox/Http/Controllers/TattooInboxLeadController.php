<?php

namespace Modules\TattooInbox\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TattooInbox\Models\TattooInboxLead;

class TattooInboxLeadController extends Controller
{
    public function index(Request $request)
    {
        $records = TattooInboxLead::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->search);

                $q->where(function ($sub) use ($search) {
                    $sub->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('instagram_username', 'like', "%{$search}%")
                        ->orWhere('external_user_id', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('channel'), function ($q) use ($request) {
                $q->where('channel', $request->channel);
            })
            ->latest('id')
            ->paginate(20);

        return response()->json($records);
    }

    public function show($id)
    {
        $record = TattooInboxLead::with([
            'conversations.messages' => function ($q) {
                $q->orderBy('id', 'asc');
            },
            'conversations.latestQuote',
        ])->findOrFail($id);

        return response()->json($record);
    }
}