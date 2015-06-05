<?php namespace Rapiro\OAuth2Server\Storage; 

use League\OAuth2\Server\Storage\AbstractStorage;
use Illuminate\Database\ConnectionResolverInterface as Resolver;

abstract class BaseStorage extends AbstractStorage {
    /**
     * @var \Illuminate\Database\ConnectionResolverInterface
     */
    protected $resolver;
    /**
     * @var string
     */
    protected $connectionName;
    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
        $this->connectionName = null;
    }
    public function setResolver(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }
    public function getResolver()
    {
        return $this->resolver;
    }
    public function setConnectionName($connectionName)
    {
        $this->connectionName = $connectionName;
    }
    protected function getConnection()
    {
        return $this->resolver->connection($this->connectionName);
    }
}