<?php

/**
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 */

namespace Designcise\PDO;

use PDO;
use PDOException;

use function compact;

class MySQLPDOWrapper implements PDOWrapperInterface
{
    use PDOWrapperTrait;

    private ?PDO $db = null;

    private array $config;
    
    /**
     * @param string $host
     * @param string $name
     * @param string $user
     * @param string $pswd
     * @param null|int $port
     * @param array $options
     */
    public function __construct(
        string $host,
        string $name,
        string $user,
        string $pswd,
        ?int $port = null,
        array $options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    ) {
        if (! isset($options[PDO::ATTR_ERRMODE])) {
            $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        }
        
        $this->config = compact('host', 'name', 'user', 'pswd', 'port', 'options');
    }
    
    /**
     * {@inheritdoc}
     *
     * @throws PDOException
     */
    public function connect(): void
    {
        if ($this->db) {
            return;
        }
        
        $port = (null !== $this->config['port']) ? "port={$this->config['port']};" : '';

        $this->db = new PDO(
            "mysql:host={$this->config['host']};{$port}dbname={$this->config['name']};charset=utf8",
            $this->config['user'],
            $this->config['pswd'],
            $this->config['options']
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function disconnect(): void
    {
        $this->db = null;
    }

    public function lastInsertId(): string
    {
        return $this->db->lastInsertId();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getPdo(): PDO
    {
        $this->connect();
        return $this->db;
    }
}
