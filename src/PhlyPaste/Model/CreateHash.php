<?php

namespace PhlyPaste\Model;

abstract class CreateHash
{
    /**
     * Calculate a unique identifier for the paste
     *
     * Uses the microtime, language, and hash of the content to seed the 
     * identifier, and then appends a uniqid() value. This is hashed using 
     * sha256, and the first 8 characters are obtained; if a match is found, 
     * repeats the process.
     * 
     * @param  Paste $paste 
     * @param  PasteServiceInterface $service 
     * @return string
     */
    public static function generateHash(Paste $paste, PasteServiceInterface $service)
    {
        $hashSeed = sprintf(
            '%d:%s:%s', 
            microtime(true), 
            $paste->language,
            hash('sha1', $paste->content)
        );
        $result = false;
        do {
            $hashSeed .= ':' . uniqid();
            $hash      = hash('sha256', $hashSeed);
            $hash      = substr($hash, 0, 8);
            $result    = $service->exists($hash);
        } while ($result);

        return $hash;
    }
}
