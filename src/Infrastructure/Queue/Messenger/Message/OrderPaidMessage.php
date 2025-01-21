<?php

declare(strict_types=1);

namespace App\Infrastructure\Queue\Messenger\Message;

class OrderPaidMessage
{
    private int $content;

    public function __construct(int $content)
    {
        $this->content = $content;
    }

    public function getContent(): int
    {
        return $this->content;
    }
}
