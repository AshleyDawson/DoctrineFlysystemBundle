<?php

namespace AshleyDawson\DoctrineFlysystemBundle\Tests\EventListener;

use AshleyDawson\DoctrineFlysystemBundle\EventListener\StorableEventSubscriber;
use AshleyDawson\DoctrineFlysystemBundle\ORM\Mapping\StorableFieldMapper;
use AshleyDawson\DoctrineFlysystemBundle\Storage\StorageHandler;
use AshleyDawson\DoctrineFlysystemBundle\Tests\AbstractDoctrineTestCase;
use AshleyDawson\DoctrineFlysystemBundle\ORM\StorableTrait;
use Doctrine\ORM\Mapping as ORM;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\Common\EventManager;

/**
 * Class StorableTraitImpl
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Tests\EventListener
 *
 * @ORM\Entity
 */
class StorableTraitImpl
{
    use StorableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $_id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $_name;

    /**
     * Get _id
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Set _id
     *
     * @param int $id
     * @return StorableTraitImpl
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return StorableTraitImpl
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    public function getFilesystemMountPrefix()
    {
        return 'test_local';
    }
}

/**
 * Class StorableEventListenerTest
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Tests\EventListener
 */
class StorableEventListenerTest extends AbstractDoctrineTestCase
{
    /**
     * @var StorageHandler
     */
    private $_storageHandler;

    /**
     * @var EventDispatcher
     */
    private $_eventDispatcher;

    protected function setUp()
    {
        $localFilesystem = new Filesystem(new Local(TESTS_TEMP_DIR));

        $mountManager = new MountManager([
            'test_local' => $localFilesystem,
        ]);

        $this->_eventDispatcher = new EventDispatcher();

        $this->_storageHandler = new StorageHandler(
            $mountManager,
            $this->_eventDispatcher,
            true
        );
    }

    public function getEventManager()
    {
        $eventManager = new EventManager();

        $eventManager->addEventSubscriber(new StorableEventSubscriber(
            new StorableFieldMapper(),
            $this->_storageHandler
        ));

        return $eventManager;
    }

    public function testStorageFieldsMapped()
    {
        $entity = (new StorableTraitImpl())
            ->setName('Foo Bar');
        ;

        $em = $this->getEntityManager();

        $em->persist($entity);

        $em->flush();

        $em->refresh($entity);

        $fieldMappings = $em->getClassMetadata(get_class($entity))->fieldMappings;

        $this->assertArrayHasKey('_id', $fieldMappings);
        $this->assertArrayHasKey('_name', $fieldMappings);
        $this->assertArrayHasKey('fileName', $fieldMappings);
        $this->assertArrayHasKey('fileStoragePath', $fieldMappings);
        $this->assertArrayHasKey('fileSize', $fieldMappings);
        $this->assertArrayHasKey('fileMimeType', $fieldMappings);
    }

    public function testPersistenceWithoutFile()
    {
        $entity = (new StorableTraitImpl())
            ->setName('Foo Bar');
        ;

        $em = $this->getEntityManager();

        $em->persist($entity);

        $em->flush();

        $em->refresh($entity);

        $this->assertNotNull($entity->getId());
        $this->assertEquals('Foo Bar', $entity->getName());

        $this->assertNull($entity->getFileName());
        $this->assertNull($entity->getFileStoragePath());
        $this->assertNull($entity->getFileSize());
        $this->assertNull($entity->getFileMimeType());
        $this->assertNull($entity->getUploadedFile());

        $this->assertEquals('test_local', $entity->getFilesystemMountPrefix());
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityClassNames()
    {
        return [
            'AshleyDawson\DoctrineFlysystemBundle\Tests\EventListener\StorableTraitImpl',
        ];
    }
}