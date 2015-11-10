<?php

namespace AshleyDawson\DoctrineFlysystemBundle\ORM\Mapping;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Class StorableFieldMapper
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\ORM\Mapping
 */
class StorableFieldMapper implements StorableFieldMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function mapFields(ClassMetadataInfo $classMetadata)
    {
        if (! $classMetadata->hasField('fileName')) {
            $classMetadata
                ->mapField([
                    'fieldName' => 'fileName',
                    'columnName' => 'file_name',
                    'type' => 'string',
                    'length' => 255,
                    'nullable' => true,
                ])
            ;
        }

        if (! $classMetadata->hasField('fileStoragePath')) {
            $classMetadata
                ->mapField([
                    'fieldName' => 'fileStoragePath',
                    'columnName' => 'file_storage_path',
                    'type' => 'string',
                    'length' => 255,
                    'nullable' => true,
                ])
            ;
        }

        if (! $classMetadata->hasField('fileSize')) {
            $classMetadata
                ->mapField([
                    'fieldName' => 'fileSize',
                    'columnName' => 'file_size',
                    'type' => 'integer',
                    'nullable' => true,
                ])
            ;
        }

        if (! $classMetadata->hasField('fileMimeType')) {
            $classMetadata
                ->mapField([
                    'fieldName' => 'fileMimeType',
                    'columnName' => 'file_mime_type',
                    'type' => 'string',
                    'length' => 60,
                    'nullable' => true,
                ])
            ;
        }
    }
}
