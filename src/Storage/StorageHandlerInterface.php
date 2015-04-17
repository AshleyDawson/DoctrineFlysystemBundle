<?php

namespace AshleyDawson\DoctrineFlysystemBundle\Storage;

/**
 * Interface StorageHandlerInterface
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Storage
 */
interface StorageHandlerInterface
{
    /**
     * Returns TRUE if the entity class is supported
     *
     * @param string $entityClassName
     * @return bool
     * @throws \AshleyDawson\DoctrineFlysystemBundle\Exception\ClassDoesNotExistException
     */
    public function isEntitySupported($entityClassName);
}