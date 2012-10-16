<?php

namespace PhlyPaste\Model;

class ArrayTokenService implements TokenServiceInterface
{
    protected $tokens;

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function verify($token)
    {
        return in_array($token, $this->tokens);
    }
}
