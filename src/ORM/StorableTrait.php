<?php

namespace AshleyDawson\DoctrineFlysystemBundle\ORM;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class StorableTrait
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\ORM
 */
trait StorableTrait
{
    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var string
     */
    protected $fileStoragePath;

    /**
     * @var int Size in bytes
     */
    protected $fileSize;

    /**
     * @var string
     */
    protected $fileMimeType;

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected $uploadedFile;

    /**
     * Get the Flysystem filesystem mount prefix as
     * configured in https://github.com/1up-lab/OneupFlysystemBundle/blob/master/Resources/doc/filesystem_create.md#use-the-mount-manager
     *
     * <code>
     * // A single filesystem...
     *
     * public function getFilesystemMountPrefix()
     * {
     *     return 'example_filesystem_mount_prefix';
     * }
     *
     * // Or a list of filesystems...
     *
     * public function getFilesystemMountPrefix()
     * {
     *     return [
     *         'example_filesystem_mount_prefix_01',
     *         'example_filesystem_mount_prefix_02',
     *     ];
     * }
     * </code>
     *
     * @return string|array
     */
    abstract public function getFilesystemMountPrefix();

    /**
     * Get fileName
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * Get fileStoragePath
     *
     * @return string
     */
    public function getFileStoragePath()
    {
        return $this->fileStoragePath;
    }

    /**
     * Set fileStoragePath
     *
     * @param string $fileStoragePath
     * @return $this
     */
    public function setFileStoragePath($fileStoragePath)
    {
        $this->fileStoragePath = $fileStoragePath;
        return $this;
    }

    /**
     * Get fileMimeType
     *
     * @return string
     */
    public function getFileMimeType()
    {
        return $this->fileMimeType;
    }

    /**
     * Set fileMimeType
     *
     * @param string $fileMimeType
     * @return $this
     */
    public function setFileMimeType($fileMimeType)
    {
        $this->fileMimeType = $fileMimeType;
        return $this;
    }

    /**
     * Get fileSize
     *
     * @return int
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * Set fileSize
     *
     * @param int $fileSize
     * @return $this
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;
        return $this;
    }

    /**
     * Get uploadedFile
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile|null
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    /**
     * Set uploadedFile
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile|null $uploadedFile
     * @return $this
     */
    public function setUploadedFile(UploadedFile $uploadedFile = null)
    {
        $this->uploadedFile = $uploadedFile;
        return $this;
    }
}