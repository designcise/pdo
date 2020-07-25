<?php

/**
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace Designcise\PDO;

use PDO;
use PDOException;

class SQLitePDOWrapper implements PDOWrapperInterface
{
    use PDOWrapperTrait;

    private ?PDO $db = null;

    private array $config;
    
    /**
     * @param string $path
     * @param array $options
     */
    public function __construct(
        string $path,
        array $options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    ) {
        if (! isset($options[PDO::ATTR_ERRMODE])) {
            $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        }
        
        $this->config = ['path' => $path, 'options' => $options];
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

        $this->db = new PDO(
            "sqlite:{$this->config['path']}",
            null,
            null,
            $this->config['options']
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        $this->db = null;
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
