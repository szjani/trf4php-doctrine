<?php
declare(strict_types=1);

namespace trf4php\doctrine\impl;

class NullObject
{
    public function __call($name, $arguments)
    {
        throw new \BadMethodCallException('NULL found instead of an object!');
    }
}
