<?php
namespace PhlyPaste\Model;

use Zend\Paginator\Adapter\DbSelect as DbSelectPaginator;
use Zend\Paginator\Paginator;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;

class TableGatewayPasteService implements PasteServiceInterface
{
    protected $table;
    protected $hydrator;

    public function __construct(PasteTable $table)
    {
        $this->table    = $table;
        $this->hydrator = new ReflectionHydrator();
    }

    /**
     * @param  Paste $paste
     * @return Paste
     */
    public function create(Paste $paste)
    {
        $paste->hash = CreateHash::generateHash($paste, $this);
        $data = $this->hydrator->extract($paste);

        $this->table->insert($data);
        return $paste;
    }

    public function exists($hash)
    {
        $result = $this->table->select(array('hash = ?' => $hash));
        $count  = count($result);
        return (bool) $count;
    }

    /**
     * @return Paste
     */
    public function fetch($hash)
    {
        $resultset = $this->table->select(array('hash = ?' => $hash));
        if (!count($resultset)) {
            return null;
        }
        return $resultset->current();
    }

    /**
     * @return Paginator
     */
    public function fetchAll()
    {
        $select = $this->table->getSql()->select();
        $select->where(array('private = ?' => 'false'));
        $select->order(array('timestamp' => 'DESC'));

        return new Paginator(new DbSelectPaginator(
            $select,
            $this->table->getAdapter(),
            $this->table->getResultSetPrototype()
        ));
    }
}
