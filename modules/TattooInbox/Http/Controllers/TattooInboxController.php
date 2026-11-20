<?php

namespace Modules\TattooInbox\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\TattooInbox\Models\TattooInboxConversation;
use Modules\TattooInbox\Models\TattooInboxLead;
use Modules\TattooInbox\Models\TattooInboxMessage;
use Modules\TattooInbox\Services\MetaInboxService;
use Modules\TattooInbox\Services\TattooQuoteService;

class TattooInboxController extends Controller
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

    public function index()
    {
        $conversationCount = TattooInboxConversation::count();
        $leadCount = TattooInboxLead::count();
        $openCount = TattooInboxConversation::where('status', 'open')->count();
        $quotedCount = TattooInboxConversation::where('quote_status', 'quoted')->count();

        return view('tattooinbox::index', compact(
            'conversationCount',
            'leadCount',
            'openCount',
            'quotedCount'
        ));
    }

    public function conversations(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $channel = trim((string) $request->get('channel', ''));
        $status = trim((string) $request->get('status', ''));

        $conversationRows = TattooInboxConversation::query()
            ->with([
                'lead',
                'quotes',
            ])
            ->when($channel !== '', function ($q) use ($channel) {
                $q->where('channel', $channel);
            })
            ->when($status !== '', function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->whereHas('lead', function ($leadQuery) use ($search) {
                        $leadQuery->where('full_name', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%')
                            ->orWhere('external_user_id', 'like', '%' . $search . '%')
                            ->orWhere('instagram_username', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })->orWhere('channel', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get()
            ->map(function (TattooInboxConversation $conversation) {
                $latestIncoming = $conversation->messages()
                    ->where('direction', 'in')
                    ->latest('id')
                    ->first();

                $latestQuote = $conversation->quotes()
                    ->latest('id')
                    ->first();

                return [
                    'row_type' => 'conversation',
                    'id' => $conversation->id,
                    'lead_id' => $conversation->lead_id,
                    'channel' => $conversation->channel,
                    'status' => $conversation->status,
                    'quote_status' => $conversation->quote_status,
                    'last_message_at' => optional($conversation->last_message_at)->format('Y-m-d H:i'),
                    'last_message_at_raw' => optional($conversation->last_message_at)->timestamp ?? 0,
                    'last_incoming_text' => optional($latestIncoming)->message_text,
                    'lead' => $conversation->lead ? [
                        'id' => $conversation->lead->id,
                        'full_name' => $conversation->lead->full_name,
                        'phone' => $conversation->lead->phone,
                        'external_user_id' => $conversation->lead->external_user_id,
                        'instagram_username' => $conversation->lead->instagram_username,
                        'email' => $conversation->lead->email,
                        'status' => $conversation->lead->status,
                        'notes' => $conversation->lead->notes,
                    ] : null,
                    'latest_quote' => $latestQuote ? [
                        'id' => $latestQuote->id,
                        'price_min' => $latestQuote->price_min,
                        'price_max' => $latestQuote->price_max,
                        'suggested_price' => $latestQuote->suggested_price,
                        'status' => $latestQuote->status,
                    ] : null,
                ];
            })
            ->values();

        $leadQuery = TattooInboxLead::query()
            ->whereDoesntHave('conversations')
            ->when($channel !== '', function ($q) use ($channel) {
                $q->where('channel', $channel);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($leadQuery) use ($search) {
                    $leadQuery->where('full_name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('external_user_id', 'like', '%' . $search . '%')
                        ->orWhere('instagram_username', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('channel', 'like', '%' . $search . '%');
                });
            });

        if ($status !== '' && $status !== 'open') {
            $leadRows = collect();
        } else {
            $leadRows = $leadQuery
                ->orderByDesc('last_message_at')
                ->orderByDesc('id')
                ->get()
                ->map(function (TattooInboxLead $lead) {
                    return [
                        'row_type' => 'lead_only',
                        'id' => 'lead_' . $lead->id,
                        'lead_id' => $lead->id,
                        'channel' => $lead->channel,
                        'status' => 'open',
                        'quote_status' => 'pending_data',
                        'last_message_at' => optional($lead->last_message_at)->format('Y-m-d H:i'),
                        'last_message_at_raw' => optional($lead->last_message_at)->timestamp ?? 0,
                        'last_incoming_text' => 'Lead registrado sin conversación todavía.',
                        'lead' => [
                            'id' => $lead->id,
                            'full_name' => $lead->full_name,
                            'phone' => $lead->phone,
                            'external_user_id' => $lead->external_user_id,
                            'instagram_username' => $lead->instagram_username,
                            'email' => $lead->email,
                            'status' => $lead->status,
                            'notes' => $lead->notes,
                        ],
                        'latest_quote' => null,
                    ];
                })
                ->values();
        }

        $rows = $conversationRows
            ->concat($leadRows)
            ->sortByDesc('last_message_at_raw')
            ->values()
            ->map(function ($row) {
                unset($row['last_message_at_raw']);
                return $row;
            });

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }



    public function conversation($id)
    {
        if (strpos((string) $id, 'lead_') === 0) {
            $leadId = (int) str_replace('lead_', '', (string) $id);

            $lead = TattooInboxLead::findOrFail($leadId);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => 'lead_' . $lead->id,
                    'row_type' => 'lead_only',
                    'channel' => $lead->channel,
                    'status' => 'open',
                    'quote_status' => 'pending_data',
                    'lead' => [
                        'id' => $lead->id,
                        'full_name' => $lead->full_name,
                        'phone' => $lead->phone,
                        'external_user_id' => $lead->external_user_id,
                        'instagram_username' => $lead->instagram_username,
                        'email' => $lead->email,
                        'status' => $lead->status,
                        'notes' => $lead->notes,
                    ],
                    'messages' => [],
                    'quotes' => [],
                ],
            ]);
        }

        $conversation = TattooInboxConversation::query()
            ->with([
                'lead',
                'messages' => function ($q) {
                    $q->orderBy('id', 'asc');
                },
                'quotes' => function ($q) {
                    $q->latest('id');
                },
            ])
            ->findOrFail($id);

        $data = [
            'id' => $conversation->id,
            'row_type' => 'conversation',
            'channel' => $conversation->channel,
            'status' => $conversation->status,
            'quote_status' => $conversation->quote_status,
            'lead' => $conversation->lead ? [
                'id' => $conversation->lead->id,
                'full_name' => $conversation->lead->full_name,
                'phone' => $conversation->lead->phone,
                'external_user_id' => $conversation->lead->external_user_id,
                'instagram_username' => $conversation->lead->instagram_username,
                'email' => $conversation->lead->email,
                'status' => $conversation->lead->status,
                'notes' => $conversation->lead->notes,
            ] : null,
            'messages' => $conversation->messages->map(function (TattooInboxMessage $message) {
                return [
                    'id' => $message->id,
                    'direction' => $message->direction,
                    'message_type' => $message->message_type,
                    'message_text' => $message->message_text,
                    'media_url' => $message->media_url,
                    'media_mime' => $message->media_mime,
                    'status' => $message->status,
                    'is_automated' => (bool) $message->is_automated,
                    'received_at' => optional($message->received_at)->format('Y-m-d H:i'),
                    'sent_at' => optional($message->sent_at)->format('Y-m-d H:i'),
                    'created_at' => optional($message->created_at)->format('Y-m-d H:i'),
                ];
            })->values(),
            'quotes' => $conversation->quotes->map(function ($quote) {
                return [
                    'id' => $quote->id,
                    'status' => $quote->status,
                    'body_zone' => $quote->body_zone,
                    'style' => $quote->style,
                    'size_text' => $quote->size_text,
                    'size_cm' => $quote->size_cm,
                    'is_color' => (bool) $quote->is_color,
                    'is_coverup' => (bool) $quote->is_coverup,
                    'detail_level' => $quote->detail_level,
                    'has_reference' => (bool) $quote->has_reference,
                    'reference_count' => $quote->reference_count,
                    'price_min' => $quote->price_min,
                    'price_max' => $quote->price_max,
                    'suggested_price' => $quote->suggested_price,
                    'customer_idea' => $quote->customer_idea,
                    'quoted_at' => optional($quote->quoted_at)->format('Y-m-d H:i'),
                ];
            })->values(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }








    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'text' => 'required|string|max:5000',
        ]);

        $conversation = TattooInboxConversation::query()
            ->with('lead')
            ->findOrFail($id);

        if (!$conversation->lead) {
            return response()->json([
                'success' => false,
                'message' => 'La conversación no tiene lead asociado.',
            ], 422);
        }

        $text = trim((string) $request->input('text'));

        $sendResult = $this->metaInboxService->sendTextMessage(
            $conversation->channel,
            $conversation->lead->external_user_id,
            $text
        );

        $message = TattooInboxMessage::create([
            'conversation_id' => $conversation->id,
            'lead_id' => $conversation->lead->id,
            'channel' => $conversation->channel,
            'direction' => 'out',
            'message_type' => 'text',
            'external_message_id' => $sendResult['external_message_id'] ?? null,
            'message_text' => $text,
            'payload' => $sendResult,
            'is_automated' => false,
            'status' => ($sendResult['success'] ?? false) ? 'sent' : 'failed',
            'sent_at' => now(),
        ]);

        $conversation->last_outgoing_at = now();
        $conversation->last_message_at = now();
        $conversation->save();

        return response()->json([
            'success' => true,
            'message' => 'Mensaje enviado correctamente.',
            'data' => [
                'message_id' => $message->id,
                'send_result' => $sendResult,
            ],
        ]);
    }

    public function recalculateQuote($id)
    {
        $conversation = TattooInboxConversation::findOrFail($id);

        $result = $this->quoteService->buildOrUpdateQuote($conversation);

        return response()->json([
            'success' => true,
            'message' => 'Cotización recalculada correctamente.',
            'data' => $result,
        ]);
    }

    public function takeOver($id)
    {
        $conversation = TattooInboxConversation::findOrFail($id);

        $conversation->bot_enabled = false;
        $conversation->taken_at = now();
        $conversation->taken_by_user_id = optional(auth()->user())->id;
        $conversation->save();

        return response()->json([
            'success' => true,
            'message' => 'Conversación tomada por humano.',
        ]);
    }

    public function reactivateBot($id)
    {
        $conversation = TattooInboxConversation::findOrFail($id);

        $conversation->bot_enabled = true;
        $conversation->save();

        return response()->json([
            'success' => true,
            'message' => 'Bot reactivado correctamente.',
        ]);
    }

    public function updateStage(Request $request, $id)
    {
        $request->validate([
            'stage' => 'required|string|max:100',
        ]);

        $conversation = TattooInboxConversation::findOrFail($id);
        $conversation->stage = $request->input('stage');
        $conversation->save();

        return response()->json([
            'success' => true,
            'message' => 'Etapa actualizada correctamente.',
            'data' => [
                'id' => $conversation->id,
                'stage' => $conversation->stage,
            ],
        ]);
    }
}
