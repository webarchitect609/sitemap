<?php

namespace WebArch\Sitemap\Model\Traits;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

trait ArrayCollectionDecorator
{
    /**
     * @var ArrayCollection
     */
    protected $collection;

    /**
     * @inheritdoc
     */
    public function __construct(array $elements = [])
    {
        $this->collection = new ArrayCollection($elements);
    }

    /**
     * @inheritdoc
     */
    public function add($element)
    {
        return $this->collection->add($element);
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        return $this->collection->clear();
    }

    /**
     * @inheritdoc
     */
    public function contains($element)
    {
        return $this->collection->contains($element);
    }

    /**
     * @inheritdoc
     */
    public function isEmpty()
    {
        return $this->collection->isEmpty();
    }

    /**
     * @inheritdoc
     */
    public function remove($key)
    {
        return $this->collection->remove($key);
    }

    /**
     * @inheritdoc
     */
    public function removeElement($element)
    {
        return $this->collection->removeElement($element);
    }

    /**
     * @inheritdoc
     */
    public function containsKey($key)
    {
        return $this->collection->containsKey($key);
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        return $this->collection->get($key);
    }

    /**
     * @inheritdoc
     */
    public function getKeys()
    {
        return $this->collection->getKeys();
    }

    /**
     * @inheritdoc
     */
    public function getValues()
    {
        return $this->collection->getValues();
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        return $this->collection->set($key, $value);
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return $this->collection->toArray();
    }

    /**
     * @inheritdoc
     */
    public function first()
    {
        return $this->collection->first();
    }

    /**
     * @inheritdoc
     */
    public function last()
    {
        return $this->collection->last();
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->collection->key();
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->collection->current();
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        return $this->collection->next();
    }

    /**
     * @inheritdoc
     */
    public function exists(Closure $p)
    {
        return $this->collection->exists($p);
    }

    /**
     * @inheritdoc
     */
    public function filter(Closure $p)
    {
        return $this->collection->filter($p);
    }

    /**
     * @inheritdoc
     */
    public function forAll(Closure $p)
    {
        return $this->collection->forAll($p);
    }

    /**
     * @inheritdoc
     */
    public function map(Closure $func)
    {
        return $this->collection->map($func);
    }

    /**
     * @inheritdoc
     */
    public function partition(Closure $p)
    {
        return $this->collection->partition($p);
    }

    /**
     * @inheritdoc
     */
    public function indexOf($element)
    {
        return $this->collection->indexOf($element);
    }

    /**
     * @inheritdoc
     */
    public function slice($offset, $length = null)
    {
        return $this->collection->slice($offset, $length);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return $this->collection->getIterator();
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return $this->collection->offsetExists($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->collection->offsetGet($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        return $this->collection->offsetSet($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        return $this->collection->offsetUnset($offset);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->collection->count();
    }

    /**
     * @inheritdoc
     */
    public function matching(Criteria $criteria)
    {
        return $this->collection->matching($criteria);
    }
}
