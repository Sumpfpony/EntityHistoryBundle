<?php

namespace Sumpfpony\EntityHistoryBundle\Registry;

class Registry
{

    protected $className;


    public function __construct($className)
    {
        $this->className = $className;
    }

    public function getClassName()
    {
        return $this->className;
    }

}