<?php
declare(strict_types=1);

namespace trf4php\doctrine\impl;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DefaultEntityManagerProxyTest extends TestCase
{
    public function testCreate()
    {
        $shared = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $proxy = new DefaultEntityManagerProxy($shared);
        self::assertSame($shared, $proxy->getWrapped());
    }

    public function testCreateWithNull()
    {
        $proxy = new DefaultEntityManagerProxy();
        self::assertInstanceOf('\trf4php\doctrine\impl\NullObject', $proxy->getWrapped());
    }

    public function testSetWrapped()
    {
        $shared = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $temp = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $proxy = new DefaultEntityManagerProxy($shared);
        $proxy->setWrapped($temp);
        self::assertSame($temp, $proxy->getWrapped());
        $proxy->setWrapped(null);
        self::assertSame($shared, $proxy->getWrapped());
    }
}
