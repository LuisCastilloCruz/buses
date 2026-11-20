<?php

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Website;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Modules\AiReception\Services\HynTattooCalendarSource;
use Modules\AiReception\Services\TattooAvailability;
use PHPUnit\Framework\TestCase;

/** Real Eloquent queries on two SQLite :memory: DBs, with Hyn context mocked. Not a MySQL/concurrency test. */
class CalendarPersistenceTest extends TestCase
{
    public function testExistingModelsAcrossTwoIsolatedCalendars()
    {
        $app = new Application(dirname(__DIR__, 3));
        $capsule = new Manager($app);
        foreach (['fixture_a', 'fixture_b'] as $name) {
            $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], $name);
            $schema = $capsule->getConnection($name)->getSchemaBuilder();
            // Projections of the existing Tattoo migrations; no new production schema is proposed.
            $schema->create('tattoo_artists', function (Blueprint $t) { $t->increments('id'); $t->boolean('active'); });
            $schema->create('tattoo_appointments', function (Blueprint $t) {
                $t->increments('id'); $t->integer('tattoo_artist_id'); $t->date('appointment_date');
                $t->time('appointment_time')->nullable(); $t->string('status');
            });
            $schema->create('tattoo_sessions', function (Blueprint $t) {
                $t->increments('id'); $t->integer('tattoo_appointment_id'); $t->date('session_date');
                $t->time('session_time')->nullable(); $t->string('status');
            });
            $capsule->getConnection($name)->table('tattoo_artists')->insert(['id' => 7, 'active' => true]);
        }
        $capsule->bootEloquent();
        $selected = 'fixture_a';
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->setMethods(['tenantName'])->getMock();
        $connection->method('tenantName')->willReturnCallback(function () use (&$selected) { return $selected; });
        $app->instance(Connection::class, $connection);
        $tenant = new Website(); $tenant->id = 1;
        $environment = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->setMethods(['tenant'])->getMock();
        $environment->method('tenant')->willReturnCallback(function () use (&$tenant) { return $tenant; });
        $reader = new HynTattooCalendarSource($environment);
        $service = new TattooAvailability($reader, function ($business, $artist) use (&$tenant) { return $business === (string) $tenant->id && $artist === '7'; });
        $policy = ['weekdays' => [6 => [['start' => '09:00', 'end' => '12:00']]], 'min_notice_minutes' => 0, 'legacy_duration_minutes' => 60];
        $now = new DateTimeImmutable('2026-11-20T09:00:00-05:00');
        $db = $capsule->getConnection('fixture_a');
        $db->table('tattoo_appointments')->insert(['id' => 1, 'tattoo_artist_id' => 7, 'appointment_date' => '2026-11-21', 'appointment_time' => '09:00:00', 'status' => 'confirmed']);
        $db->table('tattoo_sessions')->insert(['id' => 1, 'tattoo_appointment_id' => 1, 'session_date' => '2026-11-21', 'session_time' => '11:00:00', 'status' => 'pending']);
        $this->assertCount(2, $service->slots('1', '7', '2026-11-21', 30, $policy, $now));
        $db->table('tattoo_appointments')->where('id', 1)->update(['status' => 'cancelled']);
        $this->assertCount(4, $service->slots('1', '7', '2026-11-21', 30, $policy, $now));
        $db->table('tattoo_sessions')->where('id', 1)->update(['session_time' => '10:00:00']);
        $starts = array_column($service->slots('1', '7', '2026-11-21', 30, $policy, $now), 'start');
        $this->assertContains('11:00', $starts);
        $this->assertNotContains('10:00', $starts);
        $selected = 'fixture_b'; $tenant = new Website(); $tenant->id = 2;
        $this->assertCount(6, $service->slots('2', '7', '2026-11-21', 30, $policy, $now));
        $this->assertSame(1, $db->table('tattoo_appointments')->count());
        $this->assertSame(0, $capsule->getConnection('fixture_b')->table('tattoo_appointments')->count());
        $capsule->getConnection('fixture_a')->disconnect();
        $capsule->getConnection('fixture_b')->disconnect();
    }
}
