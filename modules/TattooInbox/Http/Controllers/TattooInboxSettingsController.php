<?php

namespace Modules\TattooInbox\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TattooInbox\Models\TattooInboxPromptRule;
use Modules\TattooInbox\Models\TattooInboxSetting;

class TattooInboxSettingsController extends Controller
{
    public function index()
    {
        $settings = TattooInboxSetting::current();

        $quickReplies = TattooInboxPromptRule::where('rule_type', 'quick_reply')
            ->orderBy('sort_order')
            ->get();

        $promptRules = TattooInboxPromptRule::where('rule_type', 'prompt_rule')
            ->orderBy('sort_order')
            ->get();

        $blockedTopics = TattooInboxPromptRule::where('rule_type', 'blocked_topic')
            ->orderBy('sort_order')
            ->get();

        $pricingRules = TattooInboxPromptRule::where('rule_type', 'pricing_rule')
            ->orderBy('sort_order')
            ->get();

        return view('tattooinbox::settings', compact(
            'settings',
            'quickReplies',
            'promptRules',
            'blockedTopics',
            'pricingRules'
        ));
    }

    public function update(Request $request)
    {
        $settings = TattooInboxSetting::current();

        $data = $request->validate([
            'studio_name' => 'nullable|string|max:255',
            'artist_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'contact_email' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',

            'bot_enabled' => 'nullable|boolean',
            'auto_quote_enabled' => 'nullable|boolean',
            'human_takeover_enabled' => 'nullable|boolean',
            'openai_enabled' => 'nullable|boolean',
            'openai_vision_enabled' => 'nullable|boolean',
            'openai_api_key' => 'nullable|string',
            'openai_model' => 'nullable|string|max:80',
            'openai_vision_detail' => 'nullable|in:auto,low,high',

            'assistant_prompt' => 'nullable|string',
            'assistant_tone' => 'nullable|string',
            'booking_instructions' => 'nullable|string',
            'aftercare_instructions' => 'nullable|string',

            'meta_verify_token' => 'nullable|string|max:255',
            'meta_graph_version' => 'nullable|string|max:50',
            'meta_page_access_token' => 'nullable|string',
            'whatsapp_access_token' => 'nullable|string',
            'whatsapp_phone_number_id' => 'nullable|string|max:255',
            'instagram_page_id' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:10',
        ]);

        $data['bot_enabled'] = $request->has('bot_enabled');
        $data['auto_quote_enabled'] = $request->has('auto_quote_enabled');
        $data['human_takeover_enabled'] = $request->has('human_takeover_enabled');
        $data['openai_enabled'] = $request->has('openai_enabled');
        $data['openai_vision_enabled'] = $request->has('openai_vision_enabled');

        if (empty($data['openai_model'])) {
            $data['openai_model'] = config('tattooinbox.ai.model', 'gpt-4.1-mini');
        }

        if (empty($data['openai_vision_detail'])) {
            $data['openai_vision_detail'] = 'auto';
        }

        $settings->fill($data);
        $settings->save();

        return redirect()
            ->back()
            ->with('success', 'Configuración guardada correctamente.');
    }

    public function storeRule(Request $request)
    {
        $data = $request->validate([
            'rule_type' => 'required|string|max:50',
            'rule_key' => 'required|string|max:255',
            'rule_value' => 'nullable|string',
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data['active'] = $request->has('active');

        TattooInboxPromptRule::create($data);

        return redirect()->back()->with('success', 'Regla creada correctamente.');
    }

    public function updateRule(Request $request, $id)
    {
        $rule = TattooInboxPromptRule::findOrFail($id);

        $data = $request->validate([
            'rule_key' => 'required|string|max:255',
            'rule_value' => 'nullable|string',
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data['active'] = $request->has('active');

        $rule->fill($data);
        $rule->save();

        return redirect()->back()->with('success', 'Regla actualizada correctamente.');
    }

    public function destroyRule($id)
    {
        $rule = TattooInboxPromptRule::findOrFail($id);
        $rule->delete();

        return redirect()->back()->with('success', 'Regla eliminada correctamente.');
    }
}
