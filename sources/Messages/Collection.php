<?php
namespace Ciebit\ContactUs\Messages;

use ArrayIterator;
use ArrayObject;
use Countable;
use IteratorAggregate;

class Collection implements Countable, IteratorAggregate
{
    private $messages; #:ArrayObject

    public function __construct()
    {
        $this->messages = new ArrayObject;
    }

    public function add(Message $message): self
    {
        $this->messages->append($message);
        return $this;
    }

    public function getArrayObject(): ArrayObject
    {
        return clone $this->messages;
    }

    public function getIterator(): ArrayIterator
    {
        return $this->messages->getIterator();
    }

    public function count(): int
    {
        return $this->messages->count();
    }
}
