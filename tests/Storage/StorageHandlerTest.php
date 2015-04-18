<?php

namespace AshleyDawson\DoctrineFlysystemBundle\Tests\Storage;

use AshleyDawson\DoctrineFlysystemBundle\ORM\StorableTrait;
use AshleyDawson\DoctrineFlysystemBundle\Storage\StorageHandler;
use AshleyDawson\DoctrineFlysystemBundle\Tests\AbstractDoctrineTestCase;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class StorableTraitImpl
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Tests\ORM
 *
 * @ORM\Entity
 */
class StorableTraitImpl
{
    use StorableTrait;

    public function getFilesystemAlias()
    {
        return 'dummy_filesystem_id';
    }
}

/**
 * Class DummyImpl
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Tests\ORM
 */
class DummyImpl
{
    public function getFilesystemId()
    {
        return 'dummy_filesystem_id';
    }
}

/**
 * Class StorageHandlerTest
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Tests\Storage
 */
class StorageHandlerTest extends AbstractDoctrineTestCase
{
    /**
     * @var StorageHandler
     */
    private $_storageHandler;

    protected function setUp()
    {
        $this->_storageHandler = new StorageHandler(
            $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface'),
            $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface'),
            true
        );
    }

    public function testIsEntityClassSupported()
    {
        $this->assertTrue(
            $this->_storageHandler->isEntitySupported('AshleyDawson\DoctrineFlysystemBundle\Tests\Storage\StorableTraitImpl')
        );
    }

    public function testIsEntityClassNotSupported()
    {
        $this->assertNotTrue(
            $this->_storageHandler->isEntitySupported('AshleyDawson\DoctrineFlysystemBundle\Tests\Storage\DummyImpl')
        );
    }

    public function testIsEntityClassSupportedClassNotFound()
    {
        $this->setExpectedException('AshleyDawson\DoctrineFlysystemBundle\Exception\ClassDoesNotExistException');

        $this->assertNotTrue(
            $this->_storageHandler->isEntitySupported('AshleyDawson\DoctrineFlysystemBundle\Tests\Storage\FooBarNotHere')
        );
    }

    /**
     * Get an array of entity class names that the entity
     * manager should operate on
     *
     * @return array
     */
    protected function getEntityClassNames()
    {
        return [
            'AshleyDawson\DoctrineFlysystemBundle\Tests\Storage\StorableTraitImpl',
        ];
    }
}