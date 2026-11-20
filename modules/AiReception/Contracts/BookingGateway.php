<?php

namespace Modules\AiReception\Contracts;

/** Future implementation requires authenticated actor, idempotency and transactional locking. */
interface BookingGateway
{
    public function book(array $command): void;
    public function cancel(array $command): void;
    public function reschedule(array $command): void;
}
