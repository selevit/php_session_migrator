<?php

require_once 'BaseSessionManager.php';

/**
 * Class for accessing to memcache session storage
 */
class MemcacheSessionManager extends BaseSessionManager
{
    private $host;
    private $port;
    private $conn;

    public function connect(array $params)
    {
        if (empty($params['host'])) {
            throw new InvalidArgumentException('host not found');
        }
        if (empty($params['port'])) {
            throw new InvalidArgumentException('port not found');
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
        $full_key = $this->getPrefix() . $key;
        return $this->conn->get($full_key);
    }

    public function set($key, $value)
    {
        $key = strval($key);
        if (empty($key)) {
            throw new InvalidArgumentException('key is empty');
        }
        $full_key = $this->getPrefix() . $key;
        $this->conn->set($full_key, $value);
    }

    public function delete($key)
    {
        $key = strval($key);
        if (empty($key)) {
            throw new InvalidArgumentException('key is empty');
        }
        $full_key = $this->getPrefix() . $key;
        $this->conn->delete($full_key);
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
        $keys = $this->conn->getAllKeys();
        if (!$keys) {
            return array();
        }
        $ret = array();
        foreach ($keys as $k) {
            if (starts_with($k, $this->getPrefix())) {
                $ret[] = str_replace($this->getPrefix(), '', $k); 
            }
        }
        return $ret;
    }
}
