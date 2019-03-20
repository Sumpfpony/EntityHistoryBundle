<?php
/**
 * Created by PhpStorm.
 * User: tonigurski
 * Date: 13.04.18
 * Time: 14:31
 */

namespace Sumpfpony\EntityHistoryBundle\StoreAdapter;


use Sumpfpony\EntityHistoryBundle\Model\LogInterface;

interface StoreAdapterInterface
{
    /**
     * @param string $className
     * @param int $id
     * @param [] $changeSet
     * @return void
     */
    public function createLog($className, $id, $changeSet);

    /**
     * @param string $userName
     */
    public function setUser($userName);

    /**
     * @return string
     */
    public function getUser();

    /**
     * @param string $className
     * @param int $classId
     * @param int $limit
     * @param null $offset
     * @return LogInterface[]
     */
    public function getHistories($className, $classId, $limit = 30, $offset = null);
}
