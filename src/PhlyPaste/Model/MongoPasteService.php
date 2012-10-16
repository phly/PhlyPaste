<?php
namespace PhlyPaste\Model;

use MongoCollection;
use PhlyMongo as Mongo;
use Zend\Paginator\Paginator;
use Zend\Stdlib\Hydrator\Reflection as ReflectionHydrator;

class MongoPasteService implements PasteServiceInterface
{
    protected $collection;
    protected $hydrator;

    public function __construct(MongoCollection $collection)
    {
        $this->collection = $collection;
        $this->hydrator   = new ReflectionHydrator();
    }

    /**
     * @param  Paste $paste
     * @return Paste
     */
    public function create(Paste $paste)
    {
        $paste->hash = CreateHash::generateHash($paste, $this);
        $data = $this->hydrator->extract($paste);

        $result = $this->collection->insert($data);
        return $paste;
    }

    public function exists($hash)
    {
        $result = $this->collection->findOne(array('hash' => $hash));
        if ($result === null) {
            return false;
        }
        return true;
    }

    /**
     * @return Paste
     */
    public function fetch($hash)
    {
        $data = $this->collection->findOne(array('hash' => $hash));
        if ($data === null) {
            return null;
        }
        $paste = new Paste();
        return $this->hydrator->hydrate($data, $paste);
    }

    /**
     * @return Paginator
     */
    public function fetchAll()
    {
        $cursor           = $this->collection->find(array('private' => 'false'));
        $cursor->sort(array('timestamp' => -1));
        $hydratingCursor  = new Mongo\HydratingMongoCursor($cursor, $this->hydrator, new Paste);
        $paginatorAdapter = new Mongo\HydratingPaginatorAdapter($hydratingCursor);
        return new Paginator($paginatorAdapter);
    }
}
