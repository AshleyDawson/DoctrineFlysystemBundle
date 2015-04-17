<?php

namespace AshleyDawson\DoctrineFlysystemBundle\Storage;

use AshleyDawson\DoctrineFlysystemBundle\Exception\ClassDoesNotExistException;

/**
 * Class StorageHandler
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Storage
 */
class StorageHandler implements StorageHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isEntitySupported($entityClassName)
    {
        try {
            return in_array('AshleyDawson\DoctrineFlysystemBundle\ORM\StorableTrait',
                (new \ReflectionClass($entityClassName))->getTraitNames());
        }
        catch (\ReflectionException $e) {
            throw new ClassDoesNotExistException(sprintf('Class %s does not exist', $entityClassName), 0, $e);
        }
    }
}