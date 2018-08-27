<?php

namespace Doctrine\ActiveRecord\Factory;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @license MIT
 */
interface FactoryInterface
{
    /**
     * Sets namespace used by the DAO factory method
     *
     * @param string $namespace
     */
    public function setFactoryNamespace($namespace);

    /**
     * Sets class name postfix used by the factory method
     *
     * @param string $postfix
     */
    public function setFactoryPostfix($postfix);

    /**
     * Returns absolute namespace used by the factory method
     *
     * @return string
     */
    public function getFactoryNamespace();

    /**
     * Returns class name postfix used by the factory method
     *
     * @return string
     */
    public function getFactoryPostfix();
}