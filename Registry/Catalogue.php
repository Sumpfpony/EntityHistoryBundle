<?php

namespace Sumpfpony\EntityHistoryBundle\Registry;


class Catalogue
{

    /**
     * @var Registry[]
     */
    protected $registries;


    public function __construct()
    {
        $this->registries = [];
    }


    /**
     * @param Registry $registry
     */
    public function addRegistry(Registry $registry)
    {
        $this->registries[$registry->getClassName()] = $registry;
    }

    /**
     * @param $className
     * @return Registry|mixed
     * @throws \Exception
     */
    public function getRegistry($className)
    {
        if(isset($this->registries[$className])) {
            return $this->registries[$className];
        } else {
            throw new \Exception('class '.$className. 'is not registered');
        }
    }


    /**
     * checks if the given object is configured for logging
     *
     * @param $object
     * @return bool
     */
    public function isRegistered($object){

        foreach ($this->registries as $className => $registry) {
            if($object instanceof $className) return true;
        }

        return false;
    }

}
