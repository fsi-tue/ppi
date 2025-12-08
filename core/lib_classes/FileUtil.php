<?php
class FileUtil {
    private $log = null;

    /**
     * Set the log to enable error logging.
     */
    function setLog($log) {
        $this->log = $log;
    }
    
    /**
     * Compress the protocol files given as list of file names and store them in a newly created zip archive.
     * Optionally adds a watermark message to the stored content and the zip comments and an additional readme file.
     */
    function zipFiles($listOfProtocolFileNames, $outputZipFile, $watermarkText = null, $readmeContent = null) {
        $zip = new ZipArchive();
        $zipOpened = $zip->open($outputZipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($zipOpened !== true) {
            if ($this->log !== null) {
                $this->log->error(static::class . '.php', 'Failed to create zip archive at: ' . $outputZipFile . ' (ZipArchive::open returned ' . $zipOpened . ')');
            }
            return;
        }
        
        foreach ($listOfProtocolFileNames as $fileName) {
            $fullPath = $this->getFullPathToBaseDirectory() . Constants::UPLOADED_PROTOCOLS_DIRECTORY . '/' . $fileName;
            if (file_exists($fullPath)) {
                $entryName = basename($fullPath);
                $fileContents = file_get_contents($fullPath);
                if ($watermarkText !== null) {
                    $fileContents = $this->applyWatermarkToContent($fileContents, pathinfo($fullPath, PATHINFO_EXTENSION), $watermarkText);
                }
                $zip->addFromString($entryName, $fileContents);
                if ($watermarkText !== null) {
                    $zip->setCommentName($entryName, $watermarkText);
                }
            } else {
                $this->log->warning(static::class . '.php', 'Can not add file to zip archive! File to add not found: ' . $fullPath . '!');
            }
        }
        if ($watermarkText !== null) {
            $zip->setArchiveComment($watermarkText);
        }
        if ($readmeContent !== null) {
            $zip->addFromString('README.md', $readmeContent);
            if ($watermarkText !== null) {
                $zip->setCommentName('README.md', $watermarkText);
            }
        }
        $zip->close();
    }

    private function applyWatermarkToContent($fileContents, $fileExtension, $watermarkText) {
        $extension = strtolower(strval($fileExtension));
        if ($extension === 'txt') {
            $trimmed = rtrim($fileContents, "\r\n");
            return $trimmed . "\n\n" . $watermarkText . "\n";
        }
        return $fileContents;
    }
    
    /**
     * Alias for downloading a file, but with the mime-type of a zip archive.
     */
    function downloadZipFile($zipFile) {
        $this->downloadFile($zipFile, 'application/zip');
    }
    
    /**
     * Lets the user download a file with the given filepath. The file has to be of the given content type.
     * $file: Absolut path of file. E.g.: "/var/www/ [...] protocols/FILENAME.pdf"
     * $contentType: HTTP-Header: Content Type:
     */
    function downloadFile($file, $contentType) {
        $pathInfo = pathinfo($file);
        if (!(strpos($pathInfo['dirname'], Constants::TMP_ZIP_FILES_DIRECTORY) || strpos($pathInfo['dirname'], Constants::UPLOADED_PROTOCOLS_DIRECTORY))) {
            $this->log->critical(static::class . '.php', 'Attempt to download off-directory file: ' . $file . ' !');
            die();
        }
        if (in_array($pathInfo['extension'], Constants::ALLOWED_FILE_EXTENSION_DOWNLOAD) === false) {
            $this->log->critical(static::class . '.php', 'Attempt to download off-extension file: ' . $file . ' !');
            die();
        }
        if (strpos($file, '..') !== false) {
            $this->log->critical(static::class . '.php', 'Attempt to download off-directory file through path traversal: ' . $file . ' !');
            die(); 
        }

        if (file_exists($file)) {
            header('Content-Type: ' . $contentType);
            header('Content-Transfer-Encoding: Binary');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Content-Length: ' . filesize($file));
            flush();
            while (ob_get_level()) {
                ob_end_clean();
            }
            readfile($file);
            exit();
        } else {
            $this->log->warning(static::class . '.php', 'Can not download file! File not found: ' . $file . '!');
        }
    }
    
    /**
     * Gets the current PPI root directory file path.
     */
    function getFullPathToBaseDirectory() {
        list($scriptPath) = get_included_files();
        return dirname($scriptPath) . '/';
    }
    
    /**
     * Checks if the given path has the given file extension.
     * $extension: string or array of string
     */
    function strEndsWith($path, $extension) {
        if (!is_array($extension)) {
            $extension = array($extension);
        }
        foreach ($extension as $value) {
            if(substr_compare($path, $value, -strlen($value)) === 0) {
                return true;
            }
        }
        return false;
    }
}
?>
