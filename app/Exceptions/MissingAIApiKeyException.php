<?php

namespace App\Exceptions;

class MissingAIApiKeyException extends \Exception
{
    public function __construct(string $provider)
    {
        parent::__construct("{$provider} API key not configured");
    }
}
