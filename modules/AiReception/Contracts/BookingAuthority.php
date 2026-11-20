<?php

namespace Modules\AiReception\Contracts;

interface BookingAuthority
{
    public function authorize($db, int $actor, int $business, bool $write, bool $lock);
    public function business($db, int $business);
}
