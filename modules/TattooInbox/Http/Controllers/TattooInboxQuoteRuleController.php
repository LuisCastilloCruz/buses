<?php

namespace Modules\TattooInbox\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\TattooInbox\Http\Requests\TattooInboxQuoteRuleRequest;
use Modules\TattooInbox\Models\TattooInboxQuoteRule;

class TattooInboxQuoteRuleController extends Controller
{
    public function index()
    {
        return response()->json(
            TattooInboxQuoteRule::query()->orderBy('rule_key')->get()
        );
    }

    public function store(TattooInboxQuoteRuleRequest $request)
    {
        foreach ($request->rules as $row) {
            TattooInboxQuoteRule::updateOrCreate(
                ['rule_key' => $row['rule_key']],
                [
                    'rule_value' => $row['rule_value'],
                    'description' => $row['description'] ?? null,
                    'active' => array_key_exists('active', $row) ? (bool) $row['active'] : true,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Reglas guardadas correctamente',
        ]);
    }
}