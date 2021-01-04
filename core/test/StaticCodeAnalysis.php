<?php
class StaticCodeAnalysis {
    private $fileUtil = null;

    function __construct($fileUtil) {
        $this->fileUtil = $fileUtil;
    }
    
    function analyze($ppiRootDirectory, $phpstanDirectory) {
        $allPhpFilesStr = $this->getAllPhpFilesUnderGivenDirectory($ppiRootDirectory);
        $command = 'php ' . $phpstanDirectory . 'phpstan.phar analyse ' . $allPhpFilesStr . ' -c ' . $phpstanDirectory . 'phpstan.neon --level=0';
        return shell_exec($command);
    }
    
    function getAllPhpFilesUnderGivenDirectory($startDirectory) {
        $retFilesStr = '';
        $files = scandir($startDirectory);
        foreach ($files as $key => $value) {
            $path = realpath($startDirectory . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path) && $this->fileUtil->strEndsWith($path, '.php')) {
                $retFilesStr .= $path . ' ';
            } else if (is_dir($path) && $value != '.' && $value != '..' && !$this->fileUtil->strEndsWith($path, 'ppi1.0')) {
                $retFilesStr .= $this->getAllPhpFilesUnderGivenDirectory($path);
            }
        }
        return $retFilesStr;
    }
}
?>
