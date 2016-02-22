<?php

namespace Doctrine\ActiveRecord\Factory;

use Doctrine\ActiveRecord\Exception\FactoryException;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class FactoryAbstract implements FactoryInterface
{
    /**
     * Namespace used by instance factory method
     *
     * @var string
     */
    protected $_factoryNamespace = '';

    /**
     * Class name postfix used by instance factory method
     *
     * @var string
     */
    protected $_factoryPostfix = '';

    /**
     * Factory exception class name
     *
     * @var string
     */
    protected $_factoryExceptionClassName = '\Doctrine\ActiveRecord\Exception\FactoryException';

    /**
     * Sets namespace used by the DAO factory method
     *
     * @param string $namespace
     */
    public function setFactoryNamespace($namespace)
    {
        $this->_factoryNamespace = (string)$namespace;
    }

    /**
     * Sets class name postfix used by the factory method
     *
     * @param string $postfix
     */
    public function setFactoryPostfix($postfix)
    {
        $this->_factoryPostfix = (string)$postfix;
    }

    /**
     * Returns absolute namespace used by the factory method
     *
     * @return string
     */
    public function getFactoryNamespace()
    {
        $result = $this->_factoryNamespace;

        if ($result && strpos($result, '\\') !== 0) {
            $result = '\\' . $result;
        }

        return $result;
    }

    /**
     * Returns class name postfix used by the factory method
     *
     * @return string
     */
    public function getFactoryPostfix()
    {
        return $this->_factoryPostfix;
    }

    /**
     * Returns complete class name
     *
     * @param string $name
     * @return string
     * @throws FactoryException
     */
    protected function getClassName($name) {
        if (empty($name)) {
            throw new $this->_factoryExceptionClassName ('$name must not be empty');
        }

        $result = $this->getFactoryNamespace() . '\\' . $name . $this->getFactoryPostfix();

        if (!class_exists($result)) {
            throw new $this->_factoryExceptionClassName ('Class "' . $result . '" does not exist');
        }

        return $result;
    }
}