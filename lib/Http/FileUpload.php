<?php

namespace Enlighten\Http;

/**
 * Represents an HTTP file submission received as part of a request.
 * This class is responsible for reading and handling file uploads of any type.
 */
class FileUpload
{
    /**
     * This is the name of the file, as published by the client.
     * Warning: user submitted, do not treat this value as truth or safe to use.
     *
     * @var string
     */
    protected $originalName;

    /**
     * This is the path to local temporary file that was created to hold this file upload.
     *
     * @var string
     */
    protected $temporaryPath;

    /**
     * Flag indicating whether the uploaded file has been moved.
     *
     * @default false
     * @var bool
     */
    protected $didMove = false;

    /**
     * Returns the current path to the uploaded file.
     * This variable is always updated to reflect the latest location.
     *
     * @var string
     */
    protected $currentPath;

    /**
     * The original file size of the uploaded temporary file, in bytes.
     * May be set to zero if there's a problem with the uploaded file.
     *
     * @default 0
     * @var int
     */
    protected $fileSize;

    /**
     * This is the file type, as published by the client.
     * Warning: user submitted, do not treat this value as truth or safe to use.
     *
     * @var string
     */
    protected $type;

    /**
     * The upload error code as reported by PHP while processing this file.
     *
     * @default UPLOAD_ERR_OK
     * @see https://secure.php.net/manual/en/features.file-upload.errors.php
     * @var int
     */
    protected $error = UPLOAD_ERR_OK;

    /**
     * Gets the name of the file, as published by the client.
     * Warning: user submitted, do not treat this value as truth or safe to use.
     *
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * Sets the name of the file, as published by the client.
     *
     * @param string $originalName
     * @return FileUpload
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;
        return $this;
    }

    /**
     * Gets the path to local temporary file that was created to hold this file upload.
     *
     * @return string
     */
    public function getTemporaryPath()
    {
        return $this->temporaryPath;
    }

    /**
     * Sets the path to local temporary file that was created to hold this file upload.
     *
     * @param string $temporaryPath
     * @return FileUpload
     */
    public function setTemporaryPath($temporaryPath)
    {
        $this->temporaryPath = $temporaryPath;

        if (!$this->didMove) {
            $this->currentPath = $temporaryPath;
            $this->fileSize = 0;

            if (file_exists($this->currentPath)) {
                $this->fileSize = filesize($temporaryPath);
            }
        }

        return $this;
    }

    /**
     * Gets the last known path to the uploaded file.
     *
     * If the file was not yet saved or moved, it will be set to the temporary file path (if one is known).
     * If the file was already moved (for example, using saveTo), it will be set to the path the file was saved to.
     *
     * @return string
     */
    public function getCurrentPath()
    {
        return $this->currentPath;
    }

    /**
     * Gets the file type, as published by the client.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the file type, as published by the client.
     *
     * @param string $type
     * @return FileUpload
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Gets the upload error code as reported by PHP while processing this file.
     *
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Gets a description of the upload error that has occurred.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        switch ($this->error) {
            case UPLOAD_ERR_OK:
                return 'File uploaded successfully.';
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the maximum file size.';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded.';
            case UPLOAD_ERR_PARTIAL:
                return 'The file was only partially uploaded.';
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
                return 'Could not create temporary file.';
            case UPLOAD_ERR_EXTENSION:
                return 'The upload was blocked.';
        }

        return 'An unknown error occured.';
    }

    /**
     * Gets whether an upload error has occured.
     *
     * @return bool
     */
    public function hasError()
    {
        return $this->error != UPLOAD_ERR_OK;
    }

    /**
     * Sets upload error code as reported by PHP while processing this file.
     *
     * @param int $error
     * @return FileUpload
     */
    public function setError($error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * Returns the size of the uploaded file.
     * Will return zero if there is a problem with the file.
     *
     * @return int File size in bytes.
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * Moves the temporary file to a given location, or copies the previously moved file to a new location.
     * If the destination file already exists, it will be overwritten. The temporary file will be deleted.
     *
     * @param string $targetPath The path to move the file to.
     * @return bool Returns true on success.
     */
    public function saveTo($targetPath)
    {
        if ($this->hasError()) {
            // We cannot process a file that has errored (empty, incomplete, blocked, ...)
            return false;
        }

        if ($this->getFileSize() <= 0) {
            // We cannot process empty source files
            return false;
        }

        $moveOk = false;

        if (!$this->didMove) {
            $moveOk = move_uploaded_file($this->getTemporaryPath(), $targetPath);
        } else {
            $moveOk = copy($this->getCurrentPath(), $targetPath);
        }

        if ($moveOk) {
            $this->didMove = true;
            $this->currentPath = $targetPath;
        }

        return $moveOk;
    }

    /**
     * Tries to create an FileUpload object for a given $fileArray (item in $_FILES array format).
     *
     * @param array $fileArray
     * @return FileUpload|null
     */
    public static function createFromFileArray(array $fileArray)
    {
        $requiredKeys = ['name', 'type', 'tmp_name', 'error'];

        foreach ($requiredKeys as $key) {
            if (!isset($fileArray[$key]) || empty($fileArray[$key])) {
                // A required key is missing in our file array, so this is invalid: return null
                return null;
            }
        }

        return (new FileUpload())
            ->setOriginalName($fileArray['name'])
            ->setType($fileArray['type'])
            ->setTemporaryPath($fileArray['tmp_name'])
            ->setError(intval($fileArray['error']));
    }
}