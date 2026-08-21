<?php

declare(strict_types=1);

namespace Quillstack\Events;

use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Says which listeners care about an event. A listener registered for a class is called for
 * that class and for anything extending or implementing it, so listening for an interface
 * catches every event carrying it.
 */
class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @var array<string, array<int, array{listener: callable, priority: int, order: int}>>
     */
    private array $listeners = [];

    private int $registered = 0;

    /**
     * @param class-string $eventClass
     * @param int $priority listeners with a higher priority are called first
     */
    public function listen(string $eventClass, callable $listener, int $priority = 0): self
    {
        $this->listeners[$eventClass][] = [
            'listener' => $listener,
            'priority' => $priority,
            // Two listeners of the same priority are called in the order they were added.
            'order' => $this->registered++,
        ];

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return iterable<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        $matching = [];

        foreach ($this->listeners as $eventClass => $registered) {
            if ($event instanceof $eventClass) {
                $matching = [...$matching, ...$registered];
            }
        }

        usort(
            $matching,
            static fn (array $a, array $b): int => [$b['priority'], $a['order']] <=> [$a['priority'], $b['order']]
        );

        return array_column($matching, 'listener');
    }
}
