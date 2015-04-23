<?php

namespace AshleyDawson\DoctrineFlysystemBundle\Storage;

use AshleyDawson\DoctrineFlysystemBundle\Event\DeleteEvent;
use AshleyDawson\DoctrineFlysystemBundle\Event\StorageEvents;
use AshleyDawson\DoctrineFlysystemBundle\Event\StoreEvent;
use AshleyDawson\DoctrineFlysystemBundle\Exception\ClassDoesNotExistException;
use AshleyDawson\DoctrineFlysystemBundle\Exception\EntityNotUsingStorableTraitException;
use AshleyDawson\DoctrineFlysystemBundle\Exception\FailedToWriteFileException;
use AshleyDawson\DoctrineFlysystemBundle\Exception\FilesystemNotFoundException;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class StorageHandler
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Storage
 */
class StorageHandler implements StorageHandlerInterface
{
    /**
     * @var array
     */
    private $_entityClassSupported = [];

    /**
     * @var MountManager
     */
    private $_mountManager;

    /**
     * @var EventDispatcherInterface
     */
    private $_eventDispatcher;

    /**
     * @var bool
     */
    private $_canDeleteOldFile;

    /**
     * Constructor
     *
     * @param MountManager $mountManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param bool $canDeleteOldFile
     */
    public function __construct(MountManager $mountManager, EventDispatcherInterface $eventDispatcher,
                                $canDeleteOldFile)
    {
        $this->_mountManager = $mountManager;
        $this->_eventDispatcher = $eventDispatcher;
        $this->_canDeleteOldFile = $canDeleteOldFile;
    }

    /**
     * {@inheritdoc}
     */
    public function store($entity)
    {
        if ( ! $this->isEntitySupported(get_class($entity))) {
            return;
        }

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile */
        $uploadedFile = $entity->getUploadedFile();

        if ( ! ($uploadedFile instanceof UploadedFile)) {
            return;
        }

        $filesystems = $this->_getFilesystemsForEntity($entity);

        $event = (new StoreEvent())
            ->setFileName($uploadedFile->getClientOriginalName())
            ->setFileStoragePath($uploadedFile->getClientOriginalName())
            ->setFileSize($uploadedFile->getSize())
            ->setFileMimeType($uploadedFile->getMimeType())
            ->setUploadedFile($uploadedFile)
            ->setFilesystems($filesystems)
        ;

        $this->_eventDispatcher->dispatch(StorageEvents::PRE_STORE, $event);

        foreach ($event->getFilesystems() as $prefix => $filesystem) {

            // Delete previous file if it exists
            if ($entity->getFileStoragePath() && $filesystem->has($entity->getFileStoragePath()) &&
                $this->_canDeleteOldFile) {
                $filesystem->delete($entity->getFileStoragePath());
            }

            $stream = fopen($uploadedFile->getRealPath(), 'r+');

            $hasWrittenFile = $filesystem->writeStream(
                $event->getFileStoragePath(),
                $stream
            );

            fclose($stream);

            if ( ! $hasWrittenFile) {
                throw new FailedToWriteFileException(
                    sprintf('Failed to write file %s using the filesystem with mount prefix %s',
                        $event->getFileStoragePath(), $prefix)
                );
            }
        }

        $this->_eventDispatcher->dispatch(StorageEvents::POST_STORE, $event);

        $entity
            ->setFileName($event->getFileName())
            ->setFileStoragePath($event->getFileStoragePath())
            ->setFileSize($event->getFileSize())
            ->setFileMimeType($event->getFileMimeType())
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($entity)
    {
        if ( ! $this->isEntitySupported(get_class($entity))) {
            return;
        }

        $filesystems = $this->_getFilesystemsForEntity($entity);

        $event = (new DeleteEvent())
            ->setFileStoragePath($entity->getFileStoragePath())
            ->setFilesystems($filesystems)
        ;

        $this->_eventDispatcher->dispatch(StorageEvents::PRE_DELETE, $event);

        foreach ($event->getFilesystems() as $filesystem) {
            if ($filesystem->has($event->getFileStoragePath()) && ($event->getFileStoragePath())) {
                $filesystem->delete($event->getFileStoragePath());
            }
        }

        $this->_eventDispatcher->dispatch(StorageEvents::POST_DELETE, $event);
    }

    /**
     * {@inheritdoc}
     */
    public function isEntitySupported($entityClassName)
    {
        if (isset($this->_entityClassSupported[$entityClassName])) {
            return $this->_entityClassSupported[$entityClassName];
        }

        $this->_entityClassSupported[$entityClassName] = false;

        try {
            $classNames = class_parents($entityClassName);
            $classNames[$entityClassName] = $entityClassName;
        }
        catch (\Exception $e) {
            throw new ClassDoesNotExistException(sprintf('Class %s does not exist', $entityClassName), 0, $e);
        }

        try {

            foreach ($classNames as $className) {

                if (in_array('AshleyDawson\DoctrineFlysystemBundle\ORM\StorableTrait',
                    (new \ReflectionClass($className))->getTraitNames())) {
                    $this->_entityClassSupported[$entityClassName] = true;
                    break;
                }
            }

            return $this->_entityClassSupported[$entityClassName];
        }
        catch (\ReflectionException $e) {
            throw new ClassDoesNotExistException(sprintf('Class %s does not exist', $entityClassName), 0, $e);
        }
    }

    /**
     * Try to get filesystem instances for entity
     *
     * @param object $entity
     * @return FilesystemInterface[]
     * @throws ClassDoesNotExistException
     * @throws EntityNotUsingStorableTraitException
     * @throws FilesystemNotFoundException
     */
    private function _getFilesystemsForEntity($entity)
    {
        if ( ! $this->isEntitySupported(get_class($entity))) {
            throw new EntityNotUsingStorableTraitException(
                sprintf('Class %s is not using the StorableTrait', get_class($entity)));
        }

        $filesystems = [];

        $entityPrefixes = is_array($entity->getFilesystemMountPrefix()) ?
            $entity->getFilesystemMountPrefix() : [$entity->getFilesystemMountPrefix()];

        foreach ($entityPrefixes as $mountPrefix) {
            try {
                $filesystems[$mountPrefix] = $this->_mountManager->getFilesystem($mountPrefix);
            }
            catch (\LogicException $e) {
                throw new FilesystemNotFoundException(
                    sprintf('Filesystem with the mount prefix %s could not be found', $mountPrefix), 0, $e);
            }
        }

        return $filesystems;
    }
}