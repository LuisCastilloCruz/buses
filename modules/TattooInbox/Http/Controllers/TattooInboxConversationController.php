<?php

namespace Modules\TattooInbox\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TattooInbox\Models\TattooInboxConversation;
use Modules\TattooInbox\Models\TattooInboxMessage;
use Modules\TattooInbox\Services\MetaInboxService;
use Modules\TattooInbox\Services\TattooQuoteService;

class TattooInboxConversationController extends Controller
{
    protected $metaInboxService;
    protected $quoteService;

    public function __construct(
        MetaInboxService $metaInboxService,
        TattooQuoteService $quoteService
    ) {
        $this->metaInboxService = $metaInboxService;
        $this->quoteService = $quoteService;
    }

    public function index(Request $request)
    {
        $records = TattooInboxConversation::with(['lead', 'latestQuote'])
            ->when($request->filled('channel'), function ($q) use ($request) {
                $q->where('channel', $request->channel);
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->latest('last_message_at')
            ->paginate(20);

        return response()->json($records);
    }

    public function show($id)
    {
        $record = TattooInboxConversation::with([
            'lead',
            'messages' => function ($q) {
                $q->orderBy('id', 'asc');
            },
            'quotes' => function ($q) {
                $q->latest('id');
            },
        ])->findOrFail($id);

        return response()->json($record);
    }

    public function sendManualMessage(Request $request, $id)
    {
        $request->validate([
            'text' => ['required', 'string'],
        ]);

        $conversation = TattooInboxConversation::with('lead')->findOrFail($id);

        $sendResult = $this->metaInboxService->sendTextMessage(
            $conversation->channel,
            $conversation->lead->external_user_id,
            $request->text
        );

        $message = TattooInboxMessage::create([
            'conversation_id' => $conversation->id,
            'lead_id' => $conversation->lead_id,
            'channel' => $conversation->channel,
            'direction' => 'out',
            'message_type' => 'text',
            'external_message_id' => $sendResult['external_message_id'] ?? null,
            'message_text' => $request->text,
            'payload' => $sendResult,
            'status' => ($sendResult['success'] ?? false) ? 'sent' : 'failed',
            'is_automated' => false,
            'sent_by_user_id' => optional(auth()->user())->id,
            'sent_at' => now(),
        ]);

        $conversation->last_outgoing_at = now();
        $conversation->last_message_at = now();
        $conversation->save();

        return response()->json([
            'success' => true,
            'send_result' => $sendResult,
            'message' => $message,
        ]);
    }

    public function recalculateQuote($id)
    {
        $conversation = TattooInboxConversation::findOrFail($id);

        $result = $this->quoteService->buildOrUpdateQuote($conversation);

        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }
}