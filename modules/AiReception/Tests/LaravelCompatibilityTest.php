<?php

use Hyn\Tenancy\Contracts\Tenant;
use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Website;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Modules\AiReception\Providers\AiReceptionServiceProvider;
use Modules\AiReception\Services\HynTattooCalendarSource;
use Modules\AiReception\Services\MetaWebhook;
use Modules\AiReception\Services\OpenAiIntakeClassifier;
use Modules\TattooInbox\Services\TenantContext;
use PHPUnit\Framework\TestCase;

/** Loads installed Laravel/Hyn classes only; never boots the application or reads .env. */
class LaravelCompatibilityTest extends TestCase
{
    private function container()
    {
        $app = new Application(dirname(__DIR__, 3));
        $app->instance('config', new Repository(['filesystems' => ['disks' => ['tenant' => ['root' => 'fixture']]]]));
        $app->instance('env', 'testing');
        return $app;
    }

    public function testPsr4ClassesAndDisabledProvider()
    {
        $this->assertTrue(class_exists(MetaWebhook::class));
        $this->assertTrue(class_exists(Modules\Tattoo\Models\TattooAppointment::class));
        $this->assertTrue(class_exists(Modules\TattooInbox\Services\MetaMessageParserService::class));
        $app = $this->container();
        $routesBefore = $app['router']->getRoutes()->count();
        $provider = new AiReceptionServiceProvider($app);
        $provider->register();
        $provider->boot();
        $this->assertSame($routesBefore, $app['router']->getRoutes()->count());
        $this->assertFalse($app->resolved('db'));
        $this->expectException(RuntimeException::class);
        $app->make(OpenAiIntakeClassifier::class);
    }

    public function testProviderAlwaysRejectsProductionEvenWhenFlagsAreSet()
    {
        $app = $this->container();
        $app->instance('env', 'production');
        $app['config']->set('aireception', ['enabled' => true, 'external_connections_enabled' => true]);
        (new AiReceptionServiceProvider($app))->register();
        $this->expectException(RuntimeException::class);
        $app->make(OpenAiIntakeClassifier::class);
    }

    public function testCalendarRejectsTenantMismatchBeforeAnyModelQuery()
    {
        $environment = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->setMethods(['tenant'])->getMock();
        $website = new Website();
        $website->id = 2;
        $environment->expects($this->once())->method('tenant')->willReturn($website);
        $this->expectException(RuntimeException::class);
        (new HynTattooCalendarSource($environment))->snapshot('1', '7', '2026-11-21');
    }

    public function testContextRestoresPreviousTenantAfterException()
    {
        $app = $this->container();
        $previous = new Website(); $previous->id = 1;
        $next = new Website(); $next->id = 2;
        $environment = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->setMethods(['tenant'])->getMock();
        $environment->expects($this->exactly(3))->method('tenant')->withConsecutive([], [$next], [$previous])
            ->willReturnOnConsecutiveCalls($previous, $next, $previous);
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->setMethods(['purge'])->getMock();
        $connection->expects($this->once())->method('purge');
        try {
            (new TenantContext($environment, $connection, $app))->run($next, function () {
                throw new RuntimeException('fixture failure');
            });
            $this->fail('Exception expected');
        } catch (RuntimeException $e) { $this->assertSame('fixture failure', $e->getMessage()); }
        $this->assertSame(['root' => 'fixture'], $app['config']->get('filesystems.disks.tenant'));
    }

    public function testContextClearsTenantWhenThereWasNoPreviousTenant()
    {
        $app = $this->container();
        $next = new Website(); $next->id = 2;
        $environment = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->setMethods(['tenant'])->getMock();
        $environment->expects($this->exactly(2))->method('tenant')->withConsecutive([], [$next])->willReturnOnConsecutiveCalls(null, $next);
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->setMethods(['purge'])->getMock();
        $connection->expects($this->once())->method('purge');
        $result = (new TenantContext($environment, $connection, $app))->run($next, function () use ($app, $next) {
            $app->instance(Tenant::class, $next);
            return 'fixture';
        });
        $this->assertSame('fixture', $result);
        $this->assertNull($app->make(Tenant::class));
    }

    public function testExistingInboxParserPreservesDestinationPerMessage()
    {
        $app = $this->container();
        \Illuminate\Support\Facades\Facade::setFacadeApplication($app);
        \Illuminate\Support\Facades\Log::swap(new class {
            public function info($message, array $context = []) {}
        });
        $entries = [];
        foreach (['fixture-channel-a', 'fixture-channel-b'] as $channel) {
            $entries[] = ['changes' => [['field' => 'messages', 'value' => ['metadata' => ['phone_number_id' => $channel],
                'messages' => [['id' => 'fixture-' . $channel, 'from' => '51900000000', 'type' => 'text', 'text' => ['body' => 'Consulta ficticia']]]]]]];
        }
        $parser = new Modules\TattooInbox\Services\MetaMessageParserService();
        $messages = $parser->parse(['object' => 'whatsapp_business_account', 'entry' => $entries]);
        $this->assertCount(2, $messages);
        $this->assertSame(['fixture-channel-a', 'fixture-channel-b'], array_column($messages, 'destination_value'));
        $this->assertSame('whatsapp_phone_number_id', $messages[0]['destination_key']);
        \Illuminate\Support\Facades\Facade::clearResolvedInstances();
    }
}
