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
     * Store a particular entity's uploaded file
     *
     * @param object $entity
     * @return bool TRUE if file was stored
     */
    public function store($entity);

    /**
     * Delete a particular entity's uploaded file
     *
     * @param object $entity
     * @return bool TRUE if file was deleted
     */
    public function delete($entity);

    /**
     * Returns TRUE if the entity class is supported
     *
     * @param string $entityClassName
     * @return bool
     * @throws \AshleyDawson\DoctrineFlysystemBundle\Exception\ClassDoesNotExistException
     */
    public function isEntitySupported($entityClassName);
}