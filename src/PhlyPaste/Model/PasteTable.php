<?php

namespace PhlyPaste\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;

class PasteTable extends AbstractTableGateway
{
    public function __construct(Adapter $adapter, $tableName = 'paste')
    {
        $this->adapter            = $adapter;
        $this->table              = $tableName;
        $this->resultSetPrototype = new HydratingResultSet(
            new ReflectionHydrator(),
            new Paste()
        );
        $this->resultSetPrototype->buffer();
        $this->initialize();
    }
}
