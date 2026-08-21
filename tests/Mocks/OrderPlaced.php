<?php

declare(strict_types=1);

namespace Quillstack\Events\Tests\Mocks;

use Quillstack\Events\StoppableEvent;

class OrderPlaced extends StoppableEvent
{
    /**
     * @var string[]
     */
    public array $handledBy = [];
}
