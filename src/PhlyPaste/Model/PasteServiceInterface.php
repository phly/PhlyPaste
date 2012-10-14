<?php
namespace PhlyPaste\Model;

interface PasteServiceInterface
{
    /**
     * @param  Paste $paste
     * @return Paste
     */
    public function create(Paste $paste);

    /**
     * @return bool
     */
    public function exists($hash);

    /**
     * @return Paste
     */
    public function fetch($hash);

    /**
     * @return Paginator
     */
    public function fetchAll();
}
