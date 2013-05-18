<?php
class McManager
{
    private $memcache = null;

    private $host = 'localhost';

    private $port = 11211;

    /**
     * @var bool
     */
    private $isConnected = false;

    public function __construct()
    {
        $this->memcache = new Memcache();
    }

    public function __destruct()
    {
        if (isset($this->memcache)) $this->memcache->close();
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function setPort($port)
    {
        $this->port = $port;
    }

    public function connect()
    {
        $this->memcache->connect($this->host, $this->port);
        $this->isConnected = true;
    }

    private function ensureConnected()
    {
        if (!$this->isConnected) {
            $this->connect();
        }
    }

    static public function getInstance()
    {
        static $instance;
        if ($instance == null)
        {
            $instance = new McManager();
            $instance->setHost(Core_Controller::getContainer()->getParameter('mc_host'));
            $instance->setPort(Core_Controller::getContainer()->getParameter('mc_port'));

        }
        return $instance;
    }

    public function set($key, $value, $expire)
    {
        $this->ensureConnected();

        if (isset($this->memcache)) {
            return $this->memcache->set(Core_Controller::getContainer()->getParameter('projectid') . '_' . $key, $value, false, $expire);
        } else {
            return false;
        }
    }

    public function get($key)
    {
        $this->ensureConnected();

        if (isset($this->memcache)) {
            return $this->memcache->get(Core_Controller::getContainer()->getParameter('projectid') . '_' . $key);
        } else {
            return false;
        }
    }

    public function delete($key)
    {
        $this->ensureConnected();

        if (isset($this->memcache)) {
            return $this->memcache->delete(Core_Controller::getContainer()->getParameter('projectid') . '_' . $key);
        } else {
            return false;
        }
    }

    /**
     * Gets a part of a stored array.
     *
     * @param $key string
     * @param $offset int[optional]
     * @param $limit int[optional]
     * @return array
     */
    public function getArray($key, $offset = 0, $limit = null)
    {
        $this->ensureConnected();

        $array = array();
        $data = $this->get($key);
        for ($i = $offset; isset($data[$i]) && (is_null($limit) || $i < $offset + $limit); $i++)
        {
            $array[] = $data[$i];
        }
        return $array;
    }

    public function getServerStatus($host, $port = 11211)
    {
        $this->ensureConnected();

        if (isset($this->memcache))
        {
            return $this->memcache->getServerStatus($host, $port);
        }
        else
        {
            return false;
        }
    }

    private function getStats()
    {
        $this->ensureConnected();

        if (isset($this->memcache))
        {
            return $this->memcache->getStats();
        }
        else
        {
            return false;
        }
    }

    public function getUptime()
    {
        $stats = $this->getStats();
        $time = $stats['uptime'];

        $days = floor($time / 86400);
        $time -= $days * 86400;

        $hours = floor($time / 3600);
        $time -= $hours * 3600;

        $minutes = floor($time / 60);
        $time -= $minutes * 60;

        $seconds = $time;

        return $days . 'd ' . $hours . 'h ' . $minutes . 'm ' . $seconds . 's';
    }

    public function getCurrentItems()
    {
        $stats = $this->getStats();

        return $stats['curr_items'];
    }

    public function getTotalItems()
    {
        $stats = $this->getStats();

        return $stats['total_items'];
    }

    public function getBytes()
    {
        $stats = $this->getStats();

        return floor($stats['bytes'] / 1024 / 1024) . 'MB';
    }

    public function getVersion()
    {
        $this->ensureConnected();

        if (isset($this->memcache))
        {
            return $this->memcache->getVersion();
        }
        else
        {
            return false;
        }
    }
}
