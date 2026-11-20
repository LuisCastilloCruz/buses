<?php

namespace Modules\AiReception\Services;

use Modules\AiReception\Contracts\BookingGateway;
use RuntimeException;

final class DisabledBookingGateway implements BookingGateway
{
    public function book(array $command): void { $this->reject(); }
    public function cancel(array $command): void { $this->reject(); }
    public function reschedule(array $command): void { $this->reject(); }

    private function reject(): void
    {
        throw new RuntimeException('Automatic booking mutations are disabled; human review required.');
    }
}
