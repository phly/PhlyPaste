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
     * @return Paste
     */
    public function fetch($identifier);

    /**
     * @return Paginator
     */
    public function fetchAll();
}
