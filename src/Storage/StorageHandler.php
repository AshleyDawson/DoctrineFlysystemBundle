<?php

namespace AshleyDawson\DoctrineFlysystemBundle\Storage;

use AshleyDawson\DoctrineFlysystemBundle\Event\StorageEvents;
use AshleyDawson\DoctrineFlysystemBundle\Event\StoreEvent;
use AshleyDawson\DoctrineFlysystemBundle\Exception\ClassDoesNotExistException;
use AshleyDawson\DoctrineFlysystemBundle\Exception\EntityNotUsingStorableTraitException;
use AshleyDawson\DoctrineFlysystemBundle\Exception\FailedToWriteFileException;
use AshleyDawson\DoctrineFlysystemBundle\Exception\FilesystemNotFoundException;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @param $canDeleteOldFile
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

        $filesystem = $this->_getFilesystemForEntity($entity);

        // Delete previous file if it exists
        if ($entity->getFileStoragePath() && $filesystem->has($entity->getFileStoragePath()) &&
            $this->_canDeleteOldFile) {
            $filesystem->delete($entity->getFileStoragePath());
        }

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile */
        $uploadedFile = $entity->getUploadedFile();

        $event = (new StoreEvent())
            ->setFileName($uploadedFile->getClientOriginalName())
            ->setFileStoragePath($uploadedFile->getClientOriginalName())
            ->setFileSize($uploadedFile->getSize())
            ->setFileMimeType($uploadedFile->getMimeType())
            ->setUploadedFile($uploadedFile)
            ->setFilesystem($filesystem)
        ;

        $this->_eventDispatcher->dispatch(StorageEvents::PRE_STORE, $event);

        $stream = fopen($uploadedFile->getRealPath(), 'r+');

        $hasWrittenFile = $event->getFilesystem()->writeStream(
            $event->getFileStoragePath(),
            $stream
        );

        fclose($stream);

        if ( ! $hasWrittenFile) {
            throw new FailedToWriteFileException(
                sprintf('Failed to write file %s using the filesystem with mount prefix %s',
                    $event->getFileStoragePath(), $entity->getFilesystemMountPrefix())
            );
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
    public function isEntitySupported($entityClassName)
    {
        if (isset($this->_entityClassSupported[$entityClassName])) {
            return $this->_entityClassSupported[$entityClassName];
        }

        try {
            return $this->_entityClassSupported[$entityClassName] = in_array(
                'AshleyDawson\DoctrineFlysystemBundle\ORM\StorableTrait',
                (new \ReflectionClass($entityClassName))->getTraitNames()
            );
        }
        catch (\ReflectionException $e) {
            throw new ClassDoesNotExistException(sprintf('Class %s does not exist', $entityClassName), 0, $e);
        }
    }

    /**
     * Try to get filesystem instance for entity
     *
     * @param object $entity
     * @return FilesystemInterface
     * @throws ClassDoesNotExistException
     * @throws EntityNotUsingStorableTraitException
     * @throws FilesystemNotFoundException
     */
    private function _getFilesystemForEntity($entity)
    {
        if ( ! $this->isEntitySupported(get_class($entity))) {
            throw new EntityNotUsingStorableTraitException(
                sprintf('Class %s is not using the StorableTrait', get_class($entity)));
        }

        $filesystem = $this->_mountManager->getFilesystem($entity->getFilesystemMountPrefix());

        if ( ! ($filesystem instanceof FilesystemInterface)) {
            throw new FilesystemNotFoundException(
                sprintf('Filesystem with the alias %s could not be found', $entity->getFilesystemMountPrefix()));
        }

        return $filesystem;
    }
}