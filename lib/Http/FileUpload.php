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
        return $this;
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
     * Tries to move the uploaded file to a specified target path.
     * This will cause the temporary file to be deleted.
     *
     * If the destination file already exists, it will be overwritten.
     *
     * This function checks to ensure that the file designated by filename is a valid upload file (meaning that it was
     * uploaded via PHP's HTTP POST upload mechanism). If the file is valid, it will be moved to the filename given by
     * destination.
     *
     * @param string $targetPath The path to move the file to.
     * @return bool Returns TRUE on success.
     */
    public function saveTo($targetPath)
    {
        return move_uploaded_file($this->getTemporaryPath(), $targetPath);
    }
}