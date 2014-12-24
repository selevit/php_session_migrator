<?php

require_once 'BaseSessionManager.php';

/**
 * Class for accessing to memcache session storage
 */
class MemcacheSessionManager extends BaseSessionManager
{
    private $prefix = 'memc.sess.key.';
    private $host;
    private $port;
    private $conn;

    public function connect(array $params)
    {
        if (empty($params['host'])) {
            throw new InvalidArgumentException('path not found');
        }
        if (empty($params['port'])) {
            throw new InvalidArgumentException('path not found');
        }
        $this->host = $params['host'];
        $this->port = $params['port'];
        $this->conn = new Memcached();
        $this->conn->addServer($this->host, $this->port);
    }

    public function disconnect()
    {
        unset($this->conn);
    }

    public function get($key)
    {
        $key = strval($key);
        if (empty($key)) {
            throw new InvalidArgumentException('key is empty');
        }
        $full_key = $this->prefix . $key;
        return $this->conn->get($full_key);
    }

    public function set($key, $value)
    {
        $key = strval($key);
        if (empty($key)) {
            throw new InvalidArgumentException('key is empty');
        }
        $full_key = $this->prefix . $key;
        $this->conn->set($full_key, $value);
    }

    public function getAllKeys()
    {
        $keys = $this->conn->getAllKeys();
        if (!$keys) {
            return array();
        }
        $ret = array();
        foreach ($keys as $k) {
            if (starts_with($k, $this->prefix)) {
                $ret[] = str_replace($this->prefix, '', $k); 
            }
        }
        return $ret;
    }
}
