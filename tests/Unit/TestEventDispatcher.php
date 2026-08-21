<?php

declare(strict_types=1);

namespace Quillstack\Events\Tests\Unit;

use Quillstack\Events\EventDispatcher;
use Quillstack\Events\ListenerProvider;
use Quillstack\Events\Tests\Mocks\HasUser;
use Quillstack\Events\Tests\Mocks\OrderPlaced;
use Quillstack\Events\Tests\Mocks\UserRegistered;
use Quillstack\UnitTests\AssertEqual;
use Quillstack\UnitTests\Types\AssertBoolean;
use Quillstack\UnitTests\Types\AssertObject;

class TestEventDispatcher
{
    private ListenerProvider $listeners;
    private EventDispatcher $dispatcher;

    public function __construct(
        private AssertEqual $assertEqual,
        private AssertBoolean $assertBoolean,
        private AssertObject $assertObject
    ) {
        $this->listeners = new ListenerProvider();
        $this->dispatcher = new EventDispatcher($this->listeners);
    }

    public function alistenerIsCalledWithTheEvent()
    {
        $seen = null;
        $this->listeners->listen(UserRegistered::class, function (UserRegistered $event) use (&$seen): void {
            $seen = $event->email;
        });

        $this->dispatcher->dispatch(new UserRegistered('radek@quillstack.com'));

        $this->assertEqual->equal('radek@quillstack.com', $seen);
    }

    public function theEventIsGivenBack()
    {
        $event = new UserRegistered('radek@quillstack.com');

        $this->assertObject->instanceOf(UserRegistered::class, $this->dispatcher->dispatch($event));
    }

    public function anEventNobodyListensForIsFine()
    {
        $this->assertObject->instanceOf(
            UserRegistered::class,
            $this->dispatcher->dispatch(new UserRegistered('nobody@example.com'))
        );
    }

    public function listenersAreCalledInOrderOfPriority()
    {
        $called = [];
        $record = static function (string $name) use (&$called): callable {
            return static function () use ($name, &$called): void {
                $called[] = $name;
            };
        };

        $this->listeners->listen(UserRegistered::class, $record('normal'));
        $this->listeners->listen(UserRegistered::class, $record('late'), -10);
        $this->listeners->listen(UserRegistered::class, $record('early'), 10);
        $this->listeners->listen(UserRegistered::class, $record('normal too'));

        $this->dispatcher->dispatch(new UserRegistered('radek@quillstack.com'));

        // Same priority keeps the order they were registered in.
        $this->assertEqual->equal(['early', 'normal', 'normal too', 'late'], $called);
    }

    /**
     * Listening for an interface catches every event carrying it, which is what lets one
     * listener answer a whole family of events.
     */
    public function alistenerForAnInterfaceHearsEveryEventCarryingIt()
    {
        $seen = null;
        $this->listeners->listen(HasUser::class, function (HasUser $event) use (&$seen): void {
            $seen = $event->getUser();
        });

        $this->dispatcher->dispatch(new UserRegistered('radek@quillstack.com'));

        $this->assertEqual->equal('radek@quillstack.com', $seen);
    }

    public function anEventCanBeCalledAnEndTo()
    {
        $this->listeners->listen(OrderPlaced::class, static function (OrderPlaced $event): void {
            $event->handledBy[] = 'first';
            $event->stopPropagation();
        });
        $this->listeners->listen(OrderPlaced::class, static function (OrderPlaced $event): void {
            $event->handledBy[] = 'second';
        });

        $event = $this->dispatcher->dispatch(new OrderPlaced());

        $this->assertEqual->equal(['first'], $event->handledBy);
        $this->assertBoolean->isTrue($event->isPropagationStopped());
    }

    public function anEventWhichWasNotStoppedReachesEveryone()
    {
        $this->listeners->listen(OrderPlaced::class, static function (OrderPlaced $event): void {
            $event->handledBy[] = 'first';
        });
        $this->listeners->listen(OrderPlaced::class, static function (OrderPlaced $event): void {
            $event->handledBy[] = 'second';
        });

        $event = $this->dispatcher->dispatch(new OrderPlaced());

        $this->assertEqual->equal(['first', 'second'], $event->handledBy);
        $this->assertBoolean->isFalse($event->isPropagationStopped());
    }

    public function listeningIsChained()
    {
        $provider = (new ListenerProvider())
            ->listen(UserRegistered::class, static fn () => null)
            ->listen(OrderPlaced::class, static fn () => null);

        $this->assertEqual->equal(1, count([...$provider->getListenersForEvent(new OrderPlaced())]));
    }
}
