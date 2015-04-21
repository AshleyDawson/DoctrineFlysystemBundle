<?php

namespace AshleyDawson\DoctrineFlysystemBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use League\Flysystem\FilesystemInterface;

/**
 * Class DeleteEvent
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Event
 */
class DeleteEvent extends Event
{
    /**
     * @var string
     */
    private $_fileStoragePath;

    /**
     * @var FilesystemInterface[]
     */
    private $_filesystems = [];

    /**
     * Get _fileStoragePath
     *
     * @return string
     */
    public function getFileStoragePath()
    {
        return $this->_fileStoragePath;
    }

    /**
     * Set _fileStoragePath
     *
     * @param string $fileStoragePath
     * @return DeleteEvent
     */
    public function setFileStoragePath($fileStoragePath)
    {
        $this->_fileStoragePath = $fileStoragePath;
        return $this;
    }

    /**
     * Get _filesystems
     *
     * @return \League\Flysystem\FilesystemInterface[]
     */
    public function getFilesystems()
    {
        return $this->_filesystems;
    }

    /**
     * Set _filesystems
     *
     * @param \League\Flysystem\FilesystemInterface[] $filesystems
     * @return DeleteEvent
     */
    public function setFilesystems(array $filesystems)
    {
        $this->_filesystems = $filesystems;
        return $this;
    }
}