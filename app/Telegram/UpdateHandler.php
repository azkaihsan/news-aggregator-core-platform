<?php

namespace App\Telegram;

use danog\MadelineProto\EventHandler;

class UpdateHandler extends EventHandler
{
    public function onUpdateNewMessage(array $update): void
    {
        if (isset($update['message']['message'])) {
            echo "New message: " . $update['message']['message'] . "\n";
        }
    }
}