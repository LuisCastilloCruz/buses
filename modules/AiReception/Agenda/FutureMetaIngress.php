<?php

namespace Modules\AiReception\Agenda;

use Modules\AiReception\Services\MetaWebhook;
final class FutureMetaIngress
{
    public function receive(string $body, string $signature, string $secret, array $trustedPhoneMap) : array
    {
        // Never call messages() on unverified input. Tenant mapping is provisioned server-side.
        return MetaWebhook::verifiedMessages($body, $signature, $secret, $trustedPhoneMap);
    }
}
