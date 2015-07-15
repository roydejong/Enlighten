<?php

namespace Enlighten\Http;

function move_uploaded_file($source, $destination)
{
    if (!copy($source, $destination)) {
        return false;
    }

    @unlink($source);

    return true;
}

class FileUploadTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSetOriginalName()
    {
        $file = new FileUpload();

        $this->assertEquals('', $file->getOriginalName(), 'Default is empty');
        $this->assertEquals($file, $file->setOriginalName('Hello.jpg'), 'Fluent API');
        $this->assertEquals('Hello.jpg', $file->getOriginalName());
    }

    public function testGetSetTemporaryPath()
    {
        $file = new FileUpload();

        $this->assertEquals('', $file->getCurrentPath(), 'Default is empty');
        $this->assertEquals('', $file->getTemporaryPath(), 'Default is empty');
        $this->assertEquals($file, $file->setTemporaryPath('/bad/path/Hello.jpg'), 'Fluent API');
        $this->assertEquals('/bad/path/Hello.jpg', $file->getTemporaryPath());
        $this->assertEquals('/bad/path/Hello.jpg', $file->getCurrentPath(), 'Current path should be set to temp path on new uploads if the file was not yet moved');
    }

    public function testFileSize()
    {
        $samplePath = realpath(__DIR__ . '/Sample') . '/upload.txt';

        if (!file_exists($samplePath) || filesize($samplePath) == 0) {
            $this->markTestSkipped('Sample upload file could not be read correctly: ' . $samplePath);
            return;
        }

        $expectedSize = filesize($samplePath);

        $file = new FileUpload();
        $this->assertEquals(0, $file->getTemporaryPath(), 'Default is zero');
        $file->setTemporaryPath($samplePath);
        $this->assertEquals($expectedSize, $file->getFileSize(), 'Determine file size on temporary path set');
        $file->setTemporaryPath('/bad/poop/test.jpg');
        $this->assertEquals(0, $file->getFileSize(), 'Bad files should be reported as zero size');
    }

    public function testGetSetType()
    {
        $file = new FileUpload();

        $this->assertEquals('', $file->getType(), 'my/file/type');
        $this->assertEquals($file, $file->setType('my/file/type'), 'Fluent API');
        $this->assertEquals('my/file/type', $file->getType());
    }

    public function testGetSetError()
    {
        $file = new FileUpload();

        $this->assertEquals(UPLOAD_ERR_OK, $file->getError(), 'Default is ERR_OK');
        $this->assertFalse($file->hasError(), 'ERR_OK is not an error');
        $this->assertEquals($file, $file->setError(UPLOAD_ERR_INI_SIZE), 'Fluent API');
        $this->assertEquals(UPLOAD_ERR_INI_SIZE, $file->getError());
        $this->assertTrue($file->hasError());
    }

    public function testGetErrorMessage()
    {
        $this->assertErrorCodeText(UPLOAD_ERR_OK, 'File uploaded successfully.');
        $this->assertErrorCodeText(UPLOAD_ERR_INI_SIZE, 'The uploaded file exceeds the maximum file size.');
        $this->assertErrorCodeText(UPLOAD_ERR_FORM_SIZE, 'The uploaded file exceeds the maximum file size.');
        $this->assertErrorCodeText(UPLOAD_ERR_NO_FILE, 'No file was uploaded.');
        $this->assertErrorCodeText(UPLOAD_ERR_PARTIAL, 'The file was only partially uploaded.');
        $this->assertErrorCodeText(UPLOAD_ERR_NO_TMP_DIR, 'Could not create temporary file.');
        $this->assertErrorCodeText(UPLOAD_ERR_CANT_WRITE, 'Could not create temporary file.');
        $this->assertErrorCodeText(UPLOAD_ERR_EXTENSION, 'The upload was blocked.');
        $this->assertErrorCodeText(-9999, 'An unknown error occured.');
    }

    private function assertErrorCodeText($code, $text)
    {
        $file = new FileUpload();
        $file->setError($code);
        $this->assertEquals($text, $file->getErrorMessage(), 'Error code text did not match');
    }

    public function testCantMoveWithError()
    {
        $samplePath = realpath(__DIR__ . '/Sample') . '/upload.txt';
        $targetPath = realpath(__DIR__ . '/Sample') . '/upload_moved.txt';

        if (!file_exists($samplePath) || filesize($samplePath) == 0) {
            $this->markTestSkipped('Sample upload file could not be read correctly: ' . $samplePath);
            return;
        }

        if (file_exists($targetPath)) {
            if (!unlink($targetPath)) {
                $this->markTestSkipped('Moved upload file remains from old test and could not be removed');
                return;
            }
        }

        $file = new FileUpload();
        $file->setTemporaryPath($samplePath);
        $file->setError(UPLOAD_ERR_CANT_WRITE);
        $this->assertFalse($file->saveTo($targetPath));
        $this->assertFileNotExists($targetPath);
    }

    public function testCantMoveWithBadFile()
    {
        $targetPath = realpath(__DIR__ . '/Sample') . '/upload_moved.txt';

        if (file_exists($targetPath)) {
            if (!unlink($targetPath)) {
                $this->markTestSkipped('Moved upload file remains from old test and could not be removed');
                return;
            }
        }

        $file = new FileUpload();
        $file->setTemporaryPath('/bogus/file/example.dats');
        $this->assertFalse($file->saveTo($targetPath));
        $this->assertFileNotExists($targetPath);
    }

    public function testMoveFile()
    {
        $tmpPath = realpath(__DIR__ . '/Sample') . '/upload.tmp';
        $targetPath = realpath(__DIR__ . '/Sample') . '/upload_moved.txt';
        $targetPath2 = realpath(__DIR__ . '/Sample') . '/upload_moved_xtracopy.txt';

        if (file_exists($tmpPath)) {
            if (!unlink($tmpPath)) {
                $this->markTestSkipped('Temp upload file remains from old test and could not be removed');
                return;
            }
        }

        file_put_contents($tmpPath, 'TESTING_123');

        if (file_exists($targetPath)) {
            if (!unlink($targetPath)) {
                $this->markTestSkipped('Moved upload file remains from old test and could not be removed');
                return;
            }
        }

        if (file_exists($targetPath2)) {
            if (!unlink($targetPath2)) {
                $this->markTestSkipped('Moved upload file (2) remains from old test and could not be removed');
                return;
            }
        }

        $expectedSize = filesize($tmpPath);

        $file = new FileUpload();
        $file->setTemporaryPath($tmpPath);

        // Initial copy
        $this->assertTrue($file->saveTo($targetPath));
        // Ensure file is copied OK and temp path is gone
        $this->assertFileExists($targetPath);
        $this->assertFileNotExists($tmpPath);
        $this->assertTrue(filesize($targetPath) == $expectedSize);
        // Ensure that the file size and current path reported are accurate
        $this->assertEquals($expectedSize, $file->getFileSize());
        $this->assertEquals($targetPath, $file->getCurrentPath());

        // Copy the file again to a second location
        $this->assertTrue($file->saveTo($targetPath2));
        $this->assertTrue(filesize($targetPath2) == $expectedSize);
        $this->assertFileExists($targetPath);
        $this->assertFileExists($targetPath2);
        $this->assertFileNotExists($tmpPath);
        $this->assertEquals($expectedSize, $file->getFileSize());
        $this->assertEquals($targetPath2, $file->getCurrentPath());

        // cleanup
        @unlink($tmpPath);
        @unlink($targetPath);
        @unlink($targetPath2);
    }

    public function testCreateFromArray()
    {
        $samplePath = realpath(__DIR__ . '/Sample') . '/upload.txt';

        if (!file_exists($samplePath) || filesize($samplePath) == 0) {
            $this->markTestSkipped('Sample upload file could not be read correctly: ' . $samplePath);
            return;
        }

        $file = FileUpload::createFromFileArray([
            'name' => 'my_name.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $samplePath,
            'error' => UPLOAD_ERR_EXTENSION,
            'size' => 1234567890
        ]);

        $this->assertEquals('my_name.jpg', $file->getOriginalName());
        $this->assertEquals('image/jpeg', $file->getType());
        $this->assertEquals(filesize($samplePath), $file->getFileSize());
        $this->assertEquals(UPLOAD_ERR_EXTENSION, $file->getError());
        $this->assertEquals($samplePath, $file->getCurrentPath());
        $this->assertEquals($samplePath, $file->getTemporaryPath());
    }

    public function testCreateFromEmptiedArray()
    {
        $testArray = [
            'name' => 'my_name.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/my/tmp/sample.png',
            'error' => UPLOAD_ERR_EXTENSION,
            'size' => 1234567890
        ];

        $this->assertNotNull(FileUpload::createFromFileArray($testArray), 'Base array should be OK');
        $testArray['name'] = '';
        $this->assertNull(FileUpload::createFromFileArray($testArray), 'Empty value should result in NULL result');
    }

    public function testCreateFromBadArray()
    {
        $file = FileUpload::createFromFileArray([
            'name' => 'my_name.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/bogus.jpg'
        ]);

        $this->assertNull($file, 'Bad array should result in null result');
    }

    public function testCreateFromSubArray()
    {
        $file = FileUpload::createFromFileArray([
            'name' => 'my_name.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => ['why', 'is', 'this', 'here'],
            'error' => UPLOAD_ERR_EXTENSION,
            'size' => 1234567890
        ]);

        $this->assertNull($file, 'Bad array should result in null result');
    }
}

