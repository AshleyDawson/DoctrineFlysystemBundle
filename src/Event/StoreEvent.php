<?php

namespace AshleyDawson\DoctrineFlysystemBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use League\Flysystem\FilesystemInterface;

/**
 * Class StoreEvent
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Event
 */
class StoreEvent extends Event
{
    /**
     * @var string
     */
    private $_fileName;

    /**
     * @var string
     */
    private $_fileStoragePath;

    /**
     * @var int
     */
    private $_fileSize;

    /**
     * @var string
     */
    private $_fileMimeType;

    /**
     * @var UploadedFile
     */
    private $_uploadedFile;

    /**
     * @var FilesystemInterface[]
     */
    private $_filesystems;

    /**
     * Get _fileName
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->_fileName;
    }

    /**
     * Set _fileName
     *
     * @param string $fileName
     * @return StoreEvent
     */
    public function setFileName($fileName)
    {
        $this->_fileName = $fileName;
        return $this;
    }

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
     * @return StoreEvent
     */
    public function setFileStoragePath($fileStoragePath)
    {
        $this->_fileStoragePath = $fileStoragePath;
        return $this;
    }

    /**
     * Get _fileSize
     *
     * @return int
     */
    public function getFileSize()
    {
        return $this->_fileSize;
    }

    /**
     * Set _fileSize
     *
     * @param int $fileSize
     * @return StoreEvent
     */
    public function setFileSize($fileSize)
    {
        $this->_fileSize = $fileSize;
        return $this;
    }

    /**
     * Get _fileMimeType
     *
     * @return string
     */
    public function getFileMimeType()
    {
        return $this->_fileMimeType;
    }

    /**
     * Set _fileMimeType
     *
     * @param string $fileMimeType
     * @return StoreEvent
     */
    public function setFileMimeType($fileMimeType)
    {
        $this->_fileMimeType = $fileMimeType;
        return $this;
    }

    /**
     * Get _uploadedFile
     *
     * @return UploadedFile
     */
    public function getUploadedFile()
    {
        return $this->_uploadedFile;
    }

    /**
     * Set _uploadedFile
     *
     * @param UploadedFile $uploadedFile
     * @return StoreEvent
     */
    public function setUploadedFile(UploadedFile $uploadedFile)
    {
        $this->_uploadedFile = $uploadedFile;
        return $this;
    }

    /**
     * Get _filesystems
     *
     * @return FilesystemInterface[]
     */
    public function getFilesystems()
    {
        return $this->_filesystems;
    }

    /**
     * Set _filesystems
     *
     * @param FilesystemInterface[] $filesystems
     * @return StoreEvent
     */
    public function setFilesystems(array $filesystems)
    {
        $this->_filesystems = $filesystems;
        return $this;
    }
}