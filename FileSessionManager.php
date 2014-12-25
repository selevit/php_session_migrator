<?php

require_once 'BaseSessionManager.php';

/**
 * Class for accessing to file session storage (PHP default)
 */
class FileSessionManager extends BaseSessionManager
{
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
        $session_filename = $this->path . $this->getPrefix() . $key;
        return file_get_contents($session_filename);
    }

    public function set($key, $value)
    {
        $key = strval($key);
        if (empty($key)) {
            throw new InvalidArgumentException('key is empty');
        }
        $session_filename = $this->path . $this->getPrefix() . $key;
        return file_put_contents($session_filename, $value);
    }

    public function delete($key)
    {
        $key = strval($key);
        if (empty($key)) {
            throw new InvalidArgumentException('key is empty');
        }
        $session_filename = $this->path . $this->getPrefix() . $key;
        return unlink($session_filename);
    }

    public function deleteAll()
    {
        $keys = $this->getAllKeys();
        $count = 0;
        foreach ($keys as $key) {
            $this->delete($key);
            $count++;
        }
        return $count;
    }

    public function getAllKeys()
    {
        $sessions = scandir($this->path);
        if (!$sessions) {
            return array();
        }
        $ret = array();
        foreach($sessions as $s) {
            if(!in_array($s, array('.', '..')) && starts_with($s, $this->getPrefix())) {
                $ret[] = str_replace($this->getPrefix(), '', $s);
            }
        }
        return $ret;
    }
}
