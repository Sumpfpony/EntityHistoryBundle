<?php

namespace Sumpfpony\EntityHistoryBundle\Registry;

class Factory
{

    public static function create(string $className)
    {
        $registry = new Registry($className);
        return $registry;
    }

}