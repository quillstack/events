<?php

declare(strict_types=1);

namespace Quillstack\Events\Tests\Mocks;

class UserRegistered implements HasUser
{
    public function __construct(public readonly string $email)
    {
        //
    }

    public function getUser(): string
    {
        return $this->email;
    }
}
