<?php

declare(strict_types=1);

namespace Quillstack\Events\Tests\Mocks;

interface HasUser
{
    public function getUser(): string;
}
