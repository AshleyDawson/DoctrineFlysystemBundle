<?php

namespace AshleyDawson\DoctrineFlysystemBundle\Tests\Storage;

use AshleyDawson\DoctrineFlysystemBundle\ORM\StorableTrait;
use AshleyDawson\DoctrineFlysystemBundle\Storage\StorageHandler;
use Doctrine\ORM\Mapping as ORM;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    public function getFilesystemMountPrefix()
    {
        return 'test_local_1';
    }
}

class StorableTraitMultipleFilesystemsImpl
{
    use StorableTrait;

    public function getFilesystemMountPrefix()
    {
        return [
            'test_local_1',
            'test_local_2'
        ];
    }
}

/**
 * Class StorableTraitInvalidFilesystemImpl
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Tests\Storage
 */
class StorableTraitInvalidFilesystemImpl
{
    use StorableTrait;

    public function getFilesystemMountPrefix()
    {
        return 'test_local_foo_invalid_bar';
    }
}

/**
 * Class DummyImpl
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Tests\ORM
 */
class DummyImpl
{
    public function getFilesystemMountPrefix()
    {
        return 'dummy_filesystem_mount_prefix';
    }
}

/**
 * Class StorageHandlerTest
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Tests\Storage
 */
class StorageHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StorageHandler
     */
    private $_storageHandler;

    protected function setUp()
    {
        mkdir(TESTS_TEMP_DIR . '/_2');

        $localFilesystemOne = new Filesystem(new Local(TESTS_TEMP_DIR));
        $localFilesystemTwo = new Filesystem(new Local(TESTS_TEMP_DIR . '/_2'));

        $mountManager = new MountManager([
            'test_local_1' => $localFilesystemOne,
            'test_local_2' => $localFilesystemTwo,
        ]);

        $this->_storageHandler = new StorageHandler(
            $mountManager,
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

    public function testStoreLocalFileHappyPath()
    {
        $uploadedFile = $this->_getSampleUploadedFile();

        $entity = (new StorableTraitImpl())
            ->setUploadedFile($uploadedFile)
        ;

        $this->_storageHandler->store($entity);

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-01.txt');

        $this->assertEquals('sample-01.txt', $entity->getFileName());
        $this->assertEquals('sample-01.txt', $entity->getFileStoragePath());
        $this->assertEquals(445, $entity->getFileSize());
        $this->assertEquals('text/plain', $entity->getFileMimeType());
    }

    public function testStoreMultipleLocalHappyPath()
    {
        $uploadedFile = $this->_getSampleUploadedFile();

        $entity = (new StorableTraitMultipleFilesystemsImpl())
            ->setUploadedFile($uploadedFile)
        ;

        $this->_storageHandler->store($entity);

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-01.txt');
        $this->assertFileExists(TESTS_TEMP_DIR . '/_2/sample-01.txt');

        $this->assertEquals('sample-01.txt', $entity->getFileName());
        $this->assertEquals('sample-01.txt', $entity->getFileStoragePath());
        $this->assertEquals(445, $entity->getFileSize());
        $this->assertEquals('text/plain', $entity->getFileMimeType());
    }

    public function testStoreFailedFileNotFound()
    {
        $this->setExpectedException('Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException');

        $uploadedFile = $this->_getSampleUploadedFile(TESTS_TEMP_DIR . '/' . md5(time() . mt_rand()));

        $entity = (new StorableTraitImpl())
            ->setUploadedFile($uploadedFile)
        ;

        $this->_storageHandler->store($entity);
    }

    public function testStoreUpdate()
    {
        $uploadedFile = $this->_getSampleUploadedFile();

        $entity = (new StorableTraitImpl())
            ->setUploadedFile($uploadedFile)
        ;

        $this->_storageHandler->store($entity);

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-01.txt');

        $uploadedFile = new UploadedFile(__DIR__ . '/../Resources/fixtures/sample-02.txt', 'sample-02.txt', 'text/plain', 334);

        $entity = (new StorableTraitImpl())
            ->setFileStoragePath('sample-01.txt')
            ->setUploadedFile($uploadedFile)
        ;

        $this->_storageHandler->store($entity);

        // Note: this behaviour will only exist if $canDeleteOldFile = true in constructor of handler
        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-02.txt');
        $this->assertFileNotExists(TESTS_TEMP_DIR . '/sample-01.txt');

        $this->assertEquals('sample-02.txt', $entity->getFileName());
        $this->assertEquals('sample-02.txt', $entity->getFileStoragePath());
        $this->assertEquals(334, $entity->getFileSize());
        $this->assertEquals('text/plain', $entity->getFileMimeType());
    }

    public function testStoreFilesystemCouldNotBeFound()
    {
        $this->setExpectedException('AshleyDawson\DoctrineFlysystemBundle\Exception\FilesystemNotFoundException');

        $uploadedFile = $this->_getSampleUploadedFile();

        $entity = (new StorableTraitInvalidFilesystemImpl())
            ->setUploadedFile($uploadedFile)
        ;

        $this->_storageHandler->store($entity);
    }

    public function testDeleteHappyPath()
    {
        $uploadedFile = $this->_getSampleUploadedFile();

        $entity = (new StorableTraitImpl())
            ->setUploadedFile($uploadedFile)
        ;

        $this->_storageHandler->store($entity);

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-01.txt');

        $this->_storageHandler->delete($entity);

        $this->assertFileNotExists(TESTS_TEMP_DIR . '/sample-01.txt');
    }

    public function testDeleteFileDoesNotExist()
    {
        $this->setExpectedException('League\Flysystem\FileNotFoundException');

        $entity = (new StorableTraitImpl())
            ->setFileStoragePath(md5(time() . mt_rand()) . '.txt')
        ;

        $this->_storageHandler->delete($entity);
    }

    public function testDeleteMultipleLocalHappyPath()
    {
        $uploadedFile = $this->_getSampleUploadedFile();

        $entity = (new StorableTraitMultipleFilesystemsImpl())
            ->setUploadedFile($uploadedFile)
        ;

        $this->_storageHandler->store($entity);

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-01.txt');
        $this->assertFileExists(TESTS_TEMP_DIR . '/_2/sample-01.txt');

        $this->_storageHandler->delete($entity);

        $this->assertFileNotExists(TESTS_TEMP_DIR . '/sample-01.txt');
        $this->assertFileNotExists(TESTS_TEMP_DIR . '/_2/sample-01.txt');
    }

    /**
     * @param string|null $overridePath
     * @return UploadedFile
     */
    private function _getSampleUploadedFile($overridePath = null)
    {
        return new UploadedFile($overridePath ?: __DIR__ . '/../Resources/fixtures/sample-01.txt', 'sample-01.txt', 'text/plain', 445);
    }

    protected function tearDown()
    {
        @unlink(TESTS_TEMP_DIR . '/sample-01.txt');
        @unlink(TESTS_TEMP_DIR . '/sample-02.txt');
        @unlink(TESTS_TEMP_DIR . '/_2/sample-01.txt');
        @unlink(TESTS_TEMP_DIR . '/_2/sample-02.txt');
        @rmdir(TESTS_TEMP_DIR . '/_2');
    }
}