<?php
/*
 * Copyright (c) 2013 Szurovecz János
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace trf4php\doctrine\impl;

use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use trf4php\doctrine\EntityManagerProxy;

/**
 * Used for transaction based EntityManagers.
 * The entity manager passed to the constructor is the default "shared" em.
 * Calling setEntityManager() is the way the change the wrapped instance,
 * unless the parameter is null: in this case the "shared" em will be restored.
 *
 * If the "shared" em is null, exception will be thrown in interaction
 * on the wrapped instance.
 *
 * @package trf4php\doctrine
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class DefaultEntityManagerProxy extends EntityManagerDecorator implements EntityManagerProxy
{
    private $shared;

    /**
     * @param EntityManagerInterface $shared
     */
    public function __construct(EntityManagerInterface $shared = null)
    {
        $this->shared = $shared !== null ? $shared : new NullObject();
        $this->wrapped = $this->shared;
    }

    /**
     * @param EntityManagerInterface $em
     */
    public function setWrapped(EntityManagerInterface $em = null)
    {
        if ($em === null) {
            $em = $this->shared;
        }
        $this->wrapped = $em;
    }

    /**
     * @return EntityManagerInterface|NullObject
     */
    public function getWrapped()
    {
        return $this->wrapped;
    }
}
