<?php

namespace AshleyDawson\DoctrineFlysystemBundle\ORM\Mapping;

use \Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Interface StorableFieldMapperInterface
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\ORM\Mapping
 */
interface StorableFieldMapperInterface
{
    /**
     * Map fields to entity by insertion into metadata
     *
     * @param ClassMetadataInfo $classMetadata
     * @return void
     */
    public function mapFields(ClassMetadataInfo $classMetadata);
}