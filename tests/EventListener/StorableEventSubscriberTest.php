<?php

namespace AshleyDawson\DoctrineFlysystemBundle\Tests\EventListener;

use AshleyDawson\DoctrineFlysystemBundle\Event\StorageEvents;
use AshleyDawson\DoctrineFlysystemBundle\Event\StoreEvent;
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
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
 * Class AbstractParentExampleImpl
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Tests\EventListener
 * @ORM\MappedSuperclass
 */
abstract class AbstractParentExampleImpl
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
     * Get _name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set _name
     *
     * @param string $name
     * @return AbstractParentExampleImpl
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
 * Class ChildExampleImpl
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Tests\EventListener
 * @ORM\Entity
 */
class ChildExampleImpl extends AbstractParentExampleImpl
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_of_birth_at", type="datetime")
     */
    private $_dateOfBirth;

    /**
     * Get _dateOfBirth
     *
     * @return \DateTime
     */
    public function getDateOfBirth()
    {
        return $this->_dateOfBirth;
    }

    /**
     * Set _dateOfBirth
     *
     * @param \DateTime $dateOfBirth
     * @return ChildExampleImpl
     */
    public function setDateOfBirth(\DateTime $dateOfBirth)
    {
        $this->_dateOfBirth = $dateOfBirth;
        return $this;
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
            ->setName('Foo Bar')
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
            ->setName('Foo Bar')
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

    public function testPersistenceWithUploadedFile()
    {
        $entity = (new StorableTraitImpl())
            ->setName('Foo Bar')
            ->setUploadedFile(new UploadedFile(__DIR__ . '/../Resources/fixtures/sample-01.txt', 'sample-01.txt', 'text/plain', 445))
        ;

        $em = $this->getEntityManager();

        $em->persist($entity);

        $em->flush();

        $em->refresh($entity);

        $this->assertNotNull($entity->getId());
        $this->assertEquals('Foo Bar', $entity->getName());

        $this->assertEquals('sample-01.txt', $entity->getFileName());
        $this->assertEquals('sample-01.txt', $entity->getFileStoragePath());
        $this->assertEquals(445, $entity->getFileSize());
        $this->assertEquals('text/plain', $entity->getFileMimeType());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\UploadedFile', $entity->getUploadedFile());

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-01.txt');
    }

    public function testUpdatingWithoutUploadedFile()
    {
        $entity = (new StorableTraitImpl())
            ->setName('Foo Bar')
            ->setUploadedFile(new UploadedFile(__DIR__ . '/../Resources/fixtures/sample-01.txt', 'sample-01.txt', 'text/plain', 445))
        ;

        $em = $this->getEntityManager();

        $em->persist($entity);

        $em->flush();

        $em->refresh($entity);

        $this->assertNotNull($entity->getId());
        $this->assertEquals('Foo Bar', $entity->getName());

        $this->assertEquals('sample-01.txt', $entity->getFileName());
        $this->assertEquals('sample-01.txt', $entity->getFileStoragePath());
        $this->assertEquals(445, $entity->getFileSize());
        $this->assertEquals('text/plain', $entity->getFileMimeType());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\UploadedFile', $entity->getUploadedFile());

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-01.txt');

        $entity->setName('Foo Bar Baz');

        $em->persist($entity);

        $em->flush();

        $em->refresh($entity);

        $this->assertNotNull($entity->getId());
        $this->assertEquals('Foo Bar Baz', $entity->getName());

        $this->assertEquals('sample-01.txt', $entity->getFileName());
        $this->assertEquals('sample-01.txt', $entity->getFileStoragePath());
        $this->assertEquals(445, $entity->getFileSize());
        $this->assertEquals('text/plain', $entity->getFileMimeType());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\UploadedFile', $entity->getUploadedFile());

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-01.txt');
    }

    public function testUpdatingWithOnlyUploadedFile()
    {
        $entity = (new StorableTraitImpl())
            ->setName('Foo Bar')
            ->setUploadedFile(new UploadedFile(__DIR__ . '/../Resources/fixtures/sample-01.txt', 'sample-01.txt', 'text/plain', 445))
        ;

        $em = $this->getEntityManager();

        $em->persist($entity);

        $em->flush();

        $em->refresh($entity);

        $this->assertNotNull($entity->getId());
        $this->assertEquals('Foo Bar', $entity->getName());

        $this->assertEquals('sample-01.txt', $entity->getFileName());
        $this->assertEquals('sample-01.txt', $entity->getFileStoragePath());
        $this->assertEquals(445, $entity->getFileSize());
        $this->assertEquals('text/plain', $entity->getFileMimeType());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\UploadedFile', $entity->getUploadedFile());

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-01.txt');

        $entity->setUploadedFile(new UploadedFile(__DIR__ . '/../Resources/fixtures/sample-02.txt', 'sample-02.txt', 'text/plain', 334));

        $em->persist($entity);

        $em->flush();

        $em->refresh($entity);

        $this->assertNotNull($entity->getId());
        $this->assertEquals('Foo Bar', $entity->getName());

        $this->assertEquals('sample-02.txt', $entity->getFileName());
        $this->assertEquals('sample-02.txt', $entity->getFileStoragePath());
        $this->assertEquals(334, $entity->getFileSize());
        $this->assertEquals('text/plain', $entity->getFileMimeType());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\UploadedFile', $entity->getUploadedFile());

        $this->assertFileNotExists(TESTS_TEMP_DIR . '/sample-01.txt');
        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-02.txt');
    }

    public function testUpdatingWithUploadedFileAndEntityChange()
    {
        $entity = (new StorableTraitImpl())
            ->setName('Foo Bar')
            ->setUploadedFile(new UploadedFile(__DIR__ . '/../Resources/fixtures/sample-01.txt', 'sample-01.txt', 'text/plain', 445))
        ;

        $em = $this->getEntityManager();

        $em->persist($entity);

        $em->flush();

        $em->refresh($entity);

        $this->assertNotNull($entity->getId());
        $this->assertEquals('Foo Bar', $entity->getName());

        $this->assertEquals('sample-01.txt', $entity->getFileName());
        $this->assertEquals('sample-01.txt', $entity->getFileStoragePath());
        $this->assertEquals(445, $entity->getFileSize());
        $this->assertEquals('text/plain', $entity->getFileMimeType());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\UploadedFile', $entity->getUploadedFile());

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-01.txt');

        $entity
            ->setUploadedFile(new UploadedFile(__DIR__ . '/../Resources/fixtures/sample-02.txt', 'sample-02.txt', 'text/plain', 334))
            ->setName('Biz Baz Bob')
        ;

        $em->persist($entity);

        $em->flush();

        $em->refresh($entity);

        $this->assertNotNull($entity->getId());
        $this->assertEquals('Biz Baz Bob', $entity->getName());

        $this->assertEquals('sample-02.txt', $entity->getFileName());
        $this->assertEquals('sample-02.txt', $entity->getFileStoragePath());
        $this->assertEquals(334, $entity->getFileSize());
        $this->assertEquals('text/plain', $entity->getFileMimeType());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\UploadedFile', $entity->getUploadedFile());

        $this->assertFileNotExists(TESTS_TEMP_DIR . '/sample-01.txt');
        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-02.txt');
    }

    public function testDeleteWithoutFileUpload()
    {
        $entity = (new StorableTraitImpl())
            ->setName('Foo Bar Bazzle')
        ;

        $em = $this->getEntityManager();

        $em->persist($entity);

        $em->flush();

        $em->refresh($entity);

        $this->assertNotNull($entity->getId());
        $this->assertEquals('Foo Bar Bazzle', $entity->getName());

        $this->assertNull($entity->getFileName());
        $this->assertNull($entity->getFileStoragePath());
        $this->assertNull($entity->getFileSize());
        $this->assertNull($entity->getFileMimeType());
        $this->assertNull($entity->getUploadedFile());

        $this->assertEquals('test_local', $entity->getFilesystemMountPrefix());

        $em->remove($entity);

        $em->flush();

        $this->assertFalse($em->contains($entity));
    }

    public function testDeleteWithFileUpload()
    {
        $entity = (new StorableTraitImpl())
            ->setName('Foo Bar')
            ->setUploadedFile(new UploadedFile(__DIR__ . '/../Resources/fixtures/sample-01.txt', 'sample-01.txt', 'text/plain', 445))
        ;

        $em = $this->getEntityManager();

        $em->persist($entity);

        $em->flush();

        $em->refresh($entity);

        $this->assertNotNull($entity->getId());
        $this->assertEquals('Foo Bar', $entity->getName());

        $this->assertEquals('sample-01.txt', $entity->getFileName());
        $this->assertEquals('sample-01.txt', $entity->getFileStoragePath());
        $this->assertEquals(445, $entity->getFileSize());
        $this->assertEquals('text/plain', $entity->getFileMimeType());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\UploadedFile', $entity->getUploadedFile());

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-01.txt');

        $em->remove($entity);

        $em->flush();

        $this->assertFalse($em->contains($entity));
        $this->assertFileNotExists(TESTS_TEMP_DIR . '/sample-01.txt');
    }

    public function testPreStoreEvent()
    {
        $newPath = '/doctrine-flysystem/pre-store-event-test/testing.txt';

        $this->_eventDispatcher->addListener(StorageEvents::PRE_STORE, function (StoreEvent $event) use ($newPath) {
            $event->setFileStoragePath($newPath);
        });

        $entity = (new StorableTraitImpl())
            ->setName('Foo Bar')
            ->setUploadedFile(new UploadedFile(__DIR__ . '/../Resources/fixtures/sample-01.txt', 'sample-01.txt', 'text/plain', 445))
        ;

        $em = $this->getEntityManager();

        $em->persist($entity);

        $em->flush();

        $em->refresh($entity);

        $this->assertNotNull($entity->getId());
        $this->assertEquals('Foo Bar', $entity->getName());

        $this->assertEquals('sample-01.txt', $entity->getFileName());
        $this->assertEquals($newPath, $entity->getFileStoragePath());
        $this->assertEquals(445, $entity->getFileSize());
        $this->assertEquals('text/plain', $entity->getFileMimeType());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\UploadedFile', $entity->getUploadedFile());

        $this->assertFileExists(TESTS_TEMP_DIR . $newPath);
    }

    public function testEntityInheritanceWithoutFileUpload()
    {
        $entity = (new ChildExampleImpl())
            ->setDateOfBirth(new \DateTime('1987-04-08'))
            ->setName('Foobar Baz')
        ;

        $em = $this->getEntityManager();

        $em->persist($entity);

        $em->flush();

        $em->refresh($entity);

        $this->assertNotNull($entity->getId());
        $this->assertEquals('Foobar Baz', $entity->getName());
        $this->assertEquals('1987-04-08', $entity->getDateOfBirth()->format('Y-m-d'));
    }

    public function testEntityInheritanceWithFileUpload()
    {
        $entity = (new ChildExampleImpl())
            ->setDateOfBirth(new \DateTime('1987-04-09'))
            ->setName('Foobar Baz Bizzle')
            ->setUploadedFile(new UploadedFile(__DIR__ . '/../Resources/fixtures/sample-01.txt', 'sample-01.txt', 'text/plain', 445))
        ;

        $em = $this->getEntityManager();

        $em->persist($entity);

        $em->flush();

        $em->refresh($entity);

        $this->assertNotNull($entity->getId());
        $this->assertEquals('Foobar Baz Bizzle', $entity->getName());
        $this->assertEquals('1987-04-09', $entity->getDateOfBirth()->format('Y-m-d'));

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-01.txt');
    }

    protected function tearDown()
    {
        @unlink(TESTS_TEMP_DIR . '/sample-01.txt');
        @unlink(TESTS_TEMP_DIR . '/sample-02.txt');

        $filesystem = new Filesystem(new Local(TESTS_TEMP_DIR));
        $filesystem->deleteDir('/doctrine-flysystem');

        $this->getEntityManager()->getConnection()->exec('DELETE FROM StorableTraitImpl');
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityClassNames()
    {
        return [
            'AshleyDawson\DoctrineFlysystemBundle\Tests\EventListener\StorableTraitImpl',
            'AshleyDawson\DoctrineFlysystemBundle\Tests\EventListener\AbstractParentExampleImpl',
            'AshleyDawson\DoctrineFlysystemBundle\Tests\EventListener\ChildExampleImpl',
        ];
    }
}