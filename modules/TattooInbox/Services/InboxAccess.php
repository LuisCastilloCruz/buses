<?php

namespace Modules\TattooInbox\Services;

use Modules\AiReception\Native\Access;
class InboxAccess
{
    public function handle($request, \Closure $next)
    {
        if (config('aireception.native_enabled', false)) {
            app(Access::class)->business();
            $user = auth('web')->user();
            if (!$user) {
                abort(401);
            }
            $licensed = app('db')->connection('tenant')->table('modules as m')->join('module_user as u', 'u.module_id', '=', 'm.id')->where('u.user_id', 1)->where('m.value', 'tattooinbox')->exists();
            $level = $request->is('tattoo-inbox-settings*') ? 'tattoo_inbox_settings' : 'tattoo_inbox';
            if (!$licensed || !$user->modules()->where('value', 'tattooinbox')->exists() || !$user->levels()->where('value', $level)->exists()) {
                abort(403);
            }
        }
        return $next($request);
    }
}
