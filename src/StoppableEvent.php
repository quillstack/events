<?php

declare(strict_types=1);

namespace Quillstack\Events;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * An event a listener can call an end to, so the ones after it are not called.
 */
abstract class StoppableEvent implements StoppableEventInterface
{
    private bool $stopped = false;

    public function stopPropagation(): void
    {
        $this->stopped = true;
    }

    /**
     * {@inheritDoc}
     */
    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }
}
