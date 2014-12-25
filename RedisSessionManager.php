<?php

require_once 'BaseSessionManager.php';

/**
 * Class for accessing to redis session storage
 */
class RedisSessionManager extends BaseSessionManager
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
        $this->conn = new Redis();
        $result = $this->conn->connect($this->host, $this->port);

        if (!$result) {
            throw new Exception('error connect to redis server');
        }
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

    public function getAllKeys()
    {
        $keys = $this->conn->getKeys($this->getPrefix() . '*');
        if (!$keys) {
            return array();
        }
        $ret = array();
        foreach ($keys as $k) {
            $ret[] = str_replace($this->getPrefix(), '', $k); 
        }
        return $ret;
    }
}
