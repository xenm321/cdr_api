<?php

namespace App\Services;

class Mysql
{
    private $config = array();

    /**
     * @var \PDO
     */
    private $connection = null;

    private $defaultFetchMode = \PDO::FETCH_ASSOC;

    public function __construct($host, $username, $password, $dbname, array $options = array())
    {
        $this->config = array(
            'host' => $host,
            'username' => $username,
            'password' => $password,
            'dbname' => $dbname,
            'options' => $options,
        );

        $this->connect();
    }

    protected function connect()
    {
        if ($this->connection) {
            return;
        }

        $defaultOptions = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );

        $options = array_merge($defaultOptions, $this->config['options']);

        $this->connection = new \PDO(
            $this->dsn(),
            $this->config['username'],
            $this->config['password'],
            $options
        );
    }

    public function fetchRow($query, array $bind = array(), $fetchMode = null)
    {
        if ($fetchMode === null) {
            $fetchMode = $this->defaultFetchMode;
        }

        $stmt = $this->query($query, $bind);
        return $stmt->fetch($fetchMode);
    }

    public function fetchOne($query, array $bind = array())
    {
        $result = $this->fetchRow($query, $bind, \PDO::FETCH_NUM);

        if (is_array($result) && isset($result[0])) {
            return $result[0];
        }

        return null;
    }

    public function fetchAll($query, array $bind = array(), $fetchMode = null)
    {
        if ($fetchMode === null) {
            $fetchMode = $this->defaultFetchMode;
        }

        $stmt = $this->query($query, $bind);
        return $stmt->fetchAll($fetchMode);
    }

    public function query($sql, $bind = array())
    {
        if (is_array($bind)) {
            foreach ($bind as $name => $value) {
                if (!is_int($name) && !preg_match('/^:/', $name)) {
                    $newName = ":$name";
                    unset($bind[$name]);
                    $bind[$newName] = $value;
                }
            }
        }

        $sth = $this->connection->prepare($sql);
        $sth->execute($bind);
        $sth->setFetchMode(\PDO::FETCH_ASSOC);

        return $sth;
    }

    private function dsn()
    {
        return sprintf('mysql:host=%s;dbname=%s', $this->config['host'], $this->config['dbname']);
    }
}
