<?php

namespace AshleyDawson\DoctrineFlysystemBundle\Tests\ORM;

use AshleyDawson\DoctrineFlysystemBundle\ORM\StorableTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class StorableTraitImpl
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Tests\ORM
 */
class StorableTraitImpl
{
    use StorableTrait;

    public function getFilesystemMountPrefix()
    {
        return 'dummy_filesystem_mount_prefix';
    }
}

/**
 * Class StorableTraitTest
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Tests\ORM
 */
class StorableTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StorableTraitImpl
     */
    private $_storableTraitDummy;

    private $_sampleFilePath;

    protected function setUp()
    {
        $this->_sampleFilePath = __DIR__ . '/../Resources/fixtures/sample-01.txt';

        $this->_storableTraitDummy = (new StorableTraitImpl())
            ->setFileMimeType('text/plain')
            ->setFileName('sample-01.txt')
            ->setFileSize(445)
            ->setFileStoragePath('/foo/sample-01.txt')
            ->setUploadedFile(new UploadedFile($this->_sampleFilePath, 'sample-01.txt'))
        ;
    }

    public function testFilesystemMountPrefixPresent()
    {
        $this->assertEquals('dummy_filesystem_mount_prefix', $this->_storableTraitDummy->getFilesystemMountPrefix());
    }

    public function testAccessors()
    {
        $this->assertEquals('text/plain', $this->_storableTraitDummy->getFileMimeType());
        $this->assertEquals('sample-01.txt', $this->_storableTraitDummy->getFileName());
        $this->assertEquals(445, $this->_storableTraitDummy->getFileSize());
        $this->assertEquals('/foo/sample-01.txt', $this->_storableTraitDummy->getFileStoragePath());

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\UploadedFile',
            $this->_storableTraitDummy->getUploadedFile());

        $this->assertEquals(dirname($this->_sampleFilePath), $this->_storableTraitDummy->getUploadedFile()->getPath());
        $this->assertEquals(445, $this->_storableTraitDummy->getUploadedFile()->getSize());
    }
}