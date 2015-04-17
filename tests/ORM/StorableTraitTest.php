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

    public function getFilesystemId()
    {
        return 'dummy_filesystem_id';
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
        $this->_sampleFilePath = __DIR__ . '/../Resources/fixtures/sample.txt';

        $this->_storableTraitDummy = (new StorableTraitImpl())
            ->setFileMimeType('text/plain')
            ->setFileName('sample.txt')
            ->setFileSize(445)
            ->setFileStoragePath('/foo/sample.txt')
            ->setUploadedFile(new UploadedFile($this->_sampleFilePath, 'sample.txt'))
        ;
    }

    public function testFilesystemIdPresent()
    {
        $this->assertEquals('dummy_filesystem_id', $this->_storableTraitDummy->getFilesystemId());
    }

    public function testAccessors()
    {
        $this->assertEquals('text/plain', $this->_storableTraitDummy->getFileMimeType());
        $this->assertEquals('sample.txt', $this->_storableTraitDummy->getFileName());
        $this->assertEquals(445, $this->_storableTraitDummy->getFileSize());
        $this->assertEquals('/foo/sample.txt', $this->_storableTraitDummy->getFileStoragePath());

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\UploadedFile',
            $this->_storableTraitDummy->getUploadedFile());

        $this->assertEquals(dirname($this->_sampleFilePath), $this->_storableTraitDummy->getUploadedFile()->getPath());
        $this->assertEquals(445, $this->_storableTraitDummy->getUploadedFile()->getSize());
    }
}