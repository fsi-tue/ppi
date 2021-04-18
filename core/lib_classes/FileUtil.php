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
     */
    function zipFiles($listOfProtocolFileNames, $outputZipFile) {
        $zip = new ZipArchive();
        $zip->open($outputZipFile, ZipArchive::CREATE);
        
        foreach ($listOfProtocolFileNames as $fileName) {
            $fullPath = $this->getFullPathToBaseDirectory() . Constants::UPLOADED_PROTOCOLS_DIRECTORY . '/' . $fileName;
            if (file_exists($fullPath)) {
                $zip->addFromString(basename($fullPath), file_get_contents($fullPath));  
            } else {
                $this->log->warning(static::class . '.php', 'Can not add file to zip archive! File to add not found: ' . $fullPath . '!');
            }
        }
        $zip->close();
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
