<?php

require_once 'BaseSessionManager.php';

/**
 * Class for accessing to file session storage (PHP default)
 */
class FileSessionManager extends BaseSessionManager
{
    /**
     * Session key prefix (file name prefix)
     * @var string
     */
    private $prefix = 'sess_';

    /**
     * Path of the PHP session directory (ini: session_save_path)
     * @var string
     */
    private $path;

    /**
     * Connect to session storage
     * @param  array  $params array('path' => '')
     */
    public function connect(array $params)
    {
        if (empty($params['path'])) {
            throw new InvalidArgumentException('path not found');
        }
        $this->path = $params['path'];
        if (!file_exists($this->path) || !is_dir($this->path)) {
            throw new LogicException('session path directory is not found') ;
        }
        if (!is_readable($this->path)) {
            throw new LogicException('session path is not readable');
        }
    }

    public function get($key)
    {
        $key = strval($key);
        if (empty($key)) {
            throw new InvalidArgumentException('key is empty');
        }
        $session_filename = $this->path . $this->prefix . $key;
        return file_get_contents($session_filename);
    }

    public function set($key, $value)
    {
        $key = strval($key);
        if (empty($key)) {
            throw new InvalidArgumentException('key is empty');
        }
        $session_filename = $this->path . $this->prefix . $key;
        return file_put_contents($session_filename, $value);
    }

    public function getAllKeys()
    {
        $sessions = scandir($this->path);
        if (!$sessions) {
            return array();
        }
        $ret = array();
        foreach($sessions as $s) {
            if(!in_array($s, array('.', '..')) && starts_with($s, $this->prefix)) {
                $ret[] = str_replace($this->prefix, '', $s);
            }
        }
        return $ret;
    }
}