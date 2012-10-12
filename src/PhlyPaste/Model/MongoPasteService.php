<?php
namespace PhlyPaste\Model;

use MongoCollection;
use PhlyPaste\Mongo;
use Zend\Paginator\Paginator;
use Zend\Stdlib\Hydrator\ObjectProperty as ObjectPropertyHydrator;

class MongoPasteService implements PasteServiceInterface
{
    protected $collection;
    protected $hydrator;

    public function __construct(MongoCollection $collection)
    {
        $this->collection = $collection;
        $this->hydrator   = new ObjectPropertyHydrator();
    }

    /**
     * @param  Paste $paste
     * @return Paste
     */
    public function create(Paste $paste)
    {
        $data = $this->hydrator->extract($paste);
        $data['id'] = $this->getUniqId($data);

        $result = $this->collection->insert($data);
        return $this->hydrator->hydrate($data, $paste);
    }

    /**
     * @return Paste
     */
    public function fetch($identifier)
    {
        $result = $this->collection->findOne(array('id' => $identifier));
        if ($result === null) {
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

    /**
     * Calculate a unique identifier for the paste
     *
     * Uses the microtime and language to seed the identifier, and then
     * appends a uniqid() value. This is hashed using sha256, and the first
     * 8 characters are obtained; if a match is found, repeats the process.
     * 
     * @param  array $data 
     * @return string
     */
    protected function getUniqId(array $data)
    {
        $identifier = sprintf('%d:%s', microtime(true), $data['language']);
        do {
            $identifier .= uniqid();
            $hash = hash('sha256', $identifier);
            $id = substr($hash, 0, 8);
            $result = $this->collection->findOne(array('id' => $id));
        } while ($result !== null);

        return $id;
    }
}
