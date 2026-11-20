<?php

namespace Modules\AiReception\Native;

use Modules\AiReception\Contracts\BookingAuthority;
class Authority implements BookingAuthority
{
    private $module;
    public function __construct(string $module)
    {
        $this->module = $module;
    }
    public function authorize($db, int $actor, int $business, bool $write, bool $lock)
    {
        $access = app(Access::class);
        if ($business !== $access->business() || $actor !== (int) auth('web')->id()) {
            abort(403);
        }
        if ($lock) {
            $db->table('users')->where('id', $actor)->lockForUpdate()->first();
        }
        $access->require($this->module, $write ? 'book' : 'calendar');
        $configure = $this->module === 'odontologia' ? 'manage' : 'configure';
        return (object) ['role' => $access->allows($this->module, $configure) ? 'admin' : ($access->allows($this->module, 'book') ? 'reception' : 'viewer')];
    }
    public function business($db, int $business)
    {
        $company = $db->table('companies')->first();
        return (object) ['id' => $business, 'name' => $company->name, 'timezone' => 'America/Lima'];
    }
}
