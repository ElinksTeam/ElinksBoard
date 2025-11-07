<?php

namespace App\Exceptions;

class UnsupportedAIProviderException extends \Exception
{
    public function __construct(string $provider)
    {
        parent::__construct("Unsupported AI provider: {$provider}");
    }
}
