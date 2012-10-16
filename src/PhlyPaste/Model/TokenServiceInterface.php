<?php

namespace PhlyPaste\Model;

interface TokenServiceInterface
{
    public function verify($token);
}
