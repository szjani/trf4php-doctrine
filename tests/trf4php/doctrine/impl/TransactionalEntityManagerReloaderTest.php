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

use trf4php\doctrine\DoctrineTransactionManager;
use trf4php\ObservableTransactionManager;

class TransactionalEntityManagerReloaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TransactionalEntityManagerReloader
     */
    private $reloader;

    private $emFactory;

    public function setUp()
    {
        parent::setUp();
        $this->emFactory = $this->getMock('\trf4php\doctrine\EntityManagerFactory');
        $this->reloader = new TransactionalEntityManagerReloader($this->emFactory);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNotDoctrineTransactionManager()
    {
        $manager = $this->getMock('\trf4php\ObservableTransactionManager');
        $this->reloader->update($manager, 'any');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNotProxy()
    {
        $em = $this->getMock('\Doctrine\ORM\EntityManagerInterface');
        $manager = new DoctrineTransactionManager($em);
        $this->reloader->update($manager, 'any');
    }

    public function testStartTransaction()
    {
        $em = $this->getMock('\trf4php\doctrine\EntityManagerProxy');
        $manager = new DoctrineTransactionManager($em);

        $trEm = $this->getMock('\Doctrine\ORM\EntityManagerInterface');

        $this->emFactory
            ->expects(self::once())
            ->method('create')
            ->will(self::returnValue($trEm));
        $em
            ->expects(self::once())
            ->method('setWrapped')
            ->with($trEm);

        $this->reloader->update($manager, ObservableTransactionManager::PRE_BEGIN_TRANSACTION);
    }

    public function testCommit()
    {
        $em = $this->getMock('\trf4php\doctrine\EntityManagerProxy');
        $manager = new DoctrineTransactionManager($em);

        $em
            ->expects(self::once())
            ->method('setWrapped')
            ->with(null);
        $this->reloader->update($manager, ObservableTransactionManager::POST_COMMIT);
    }
}
