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

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use trf4php\doctrine\EntityManagerFactory;

class DefaultEntityManagerFactory implements EntityManagerFactory
{
    /**
     * @var mixed
     */
    private $conn;

    /**
     * @var \Doctrine\ORM\Configuration
     */
    private $config;

    /**
     * @var \Doctrine\Common\EventManager
     */
    private $eventManager;

    /**
     * @param $conn
     * @param Configuration $config
     * @param EventManager $eventManager
     */
    public function __construct($conn, Configuration $config, EventManager $eventManager = null)
    {
        $this->conn = $conn;
        $this->config = $config;
        $this->eventManager = $eventManager;
    }

    /**
     * Factory method to create EntityManager instances.
     *
     * @return EntityManagerInterface The created EntityManager.
     *
     * @throws \InvalidArgumentException
     * @throws ORMException
     */
    public function create()
    {
        return EntityManager::create($this->conn, $this->config, $this->eventManager);
    }
}
