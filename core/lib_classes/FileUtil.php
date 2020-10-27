<?php
class FileUtil {
    private $log = null;

    function setLog($log) {
        $this->log = $log;
    }
    
    function zipFiles($listOfFiles, $outputZipFile) {
        $zip = new ZipArchive();
        $zip->open($outputZipFile, ZipArchive::CREATE);
        
        foreach ($listOfFiles as $file) {
            if (file_exists($file)) {
                $zip->addFromString(basename($file), file_get_contents($file));  
            } else {
                $this->log->warning(static::class . '.php', 'Can not add file to zip archive! File to add not found: ' . $file . '!');
            }
        }
        $zip->close();
    }
    
    function downloadZipFile($zipFile) {
        $this->downloadFile($zipFile, 'application/zip');
    }
    
    function downloadFile($file, $contentType) {
        /* 
        * This download is dangerous as no checks are made.
        * TODO: implement checks
        */
        $pathInfo = pathinfo($file);
        if (strpos($pathInfo['dirname'], Constants::TMP_ZIP_FILES_DIRECTORY) === false) {
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
    
    function getFullPathToBaseDirectory() {
        list($scriptPath) = get_included_files();
        return dirname($scriptPath) . '/';
    }
}
?>
