<?php

namespace AshleyDawson\DoctrineFlysystemBundle\Tests\ORM\Mapping;

use AshleyDawson\DoctrineFlysystemBundle\ORM\Mapping\StorableFieldMapper;
use AshleyDawson\DoctrineFlysystemBundle\ORM\StorableTrait;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Class StorableTraitImpl
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Tests\ORM\Mapping
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
 * Class StorableFieldMapperTest
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Tests\ORM\Mapping
 */
class StorableFieldMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StorableFieldMapper
     */
    private $_storableFieldMapper;

    /**
     * @var ClassMetadataInfo
     */
    private $_dummyClassMetadata;

    protected function setUp()
    {
        $this->_storableFieldMapper = new StorableFieldMapper();
        $this->_dummyClassMetadata = new ClassMetadataInfo('');
    }

    public function testFieldsAreMapped()
    {
        $this->_storableFieldMapper->mapFields($this->_dummyClassMetadata);

        $this->assertCount(4, $this->_dummyClassMetadata->fieldMappings);

        $this->assertTrue(array_key_exists('fileName', $this->_dummyClassMetadata->fieldMappings));
        $this->assertTrue(array_key_exists('fileStoragePath', $this->_dummyClassMetadata->fieldMappings));
        $this->assertTrue(array_key_exists('fileSize', $this->_dummyClassMetadata->fieldMappings));
        $this->assertTrue(array_key_exists('fileMimeType', $this->_dummyClassMetadata->fieldMappings));
    }

    public function testFieldMappingsMatchTraitProperties()
    {
        $this->_storableFieldMapper->mapFields($this->_dummyClassMetadata);

        $reflectionClass = new \ReflectionClass('AshleyDawson\DoctrineFlysystemBundle\Tests\ORM\Mapping\StorableTraitImpl');

        foreach ($this->_dummyClassMetadata->fieldMappings as $mappedPropertyName => $reflectionProperty) {
            $this->assertTrue($reflectionClass->hasProperty($mappedPropertyName));
        }
    }
}