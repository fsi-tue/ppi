<?php
    class I18n {
        // map that contains all strings in the language that will be used by the internationalization (can be any of 'de', 'en')
        private $internationalization = NULL;
        
        // constructor, language to be used must be set
        function __construct($lang) {
            $this->init($lang);
        }
        
        function init($lang) {
            if (($i18nFileContent = file_get_contents($lang . '.txt', 'r')) !== FALSE) {
                $this->internationalization = $this->parseI18nFile($i18nFileContent);
            }
            if (!isset($this->internationalization)) {
                // TODO: error handling for unknown languages
            }
        }
        
        // get the internationalizon string corresponding to the given key
        function get($key) {
            $s = $this->getString($key);
            if ($s != null) {
                return $s;
            }
            return '!' . $key . '!';
        }
        
        // get the internationalizon string corresponding to the given key with replacement of placeholders
        function getWithValues($key, $values) {
            $s = $this->getString($key);
            if ($s != null) {
                for ($i = 0; $i < count($values); $i++) {
                	$s = str_replace('{' . $i . '}', $values[$i], $s);
                }
                return $s;
            }
            return '!' . $key . '!';
        }
        
        // get the internationalizon string corresponding to the given key with replacement of placeholders
        function getString($key) {
            if (!array_key_exists($key, $this->internationalization)) {
                return null;
            }
            $s = $this->internationalization[$key];
            if (isset($s) && $s != null && $s != '') {
                return $s;
            }
            return null;
        }
        
        // parse a internationalizon file
        // Splits each line at the first =
        // left part is the key for the german/english string
        function parseI18nFile($fileContent) {
            $i18n = array();
            $fileContent = str_replace('\r', '', $fileContent);
            $lines = explode("\n", $fileContent);
            foreach ($lines as &$line) {
                $cells = explode("=", $line, 2);
                if (isset($cells[0]) && isset($cells[1]) && strlen($cells[0]) > 0 && strlen($cells[1]) > 0) {
                    $i18n[$cells[0]] = $cells[1];
                }
            }
            return $i18n;
        }
    }
?>
