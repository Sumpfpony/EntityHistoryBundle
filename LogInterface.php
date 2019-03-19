<?php

namespace Sumpfpony\EntityHistoryBundle\Model;

/**
 * Interface LogInterface
 * @package Sumpfpony\EntityHistoryBundle
 */
interface LogInterface
{

    /**
     * @param string $user
     * @return LogInterface
     */
    public function setUser(string $user);


    /**
     * @param array $changeSet
     * @return LogInterface
     */
    public function setChangeSet(array $changeSet);


    /**
     * @param string $className
     * @return LogInterface
     */
    public function setClassName(string $className);


    /**
     * @param string $classId
     * @return LogInterface
     */
    public function setClassId(string $classId);


    /**
     * @param \DateTime $dateTime
     * @return LogInterface
     */
    public function setDateTime(\DateTime $dateTime);


    /**
     * @return mixed
     */
    public function getUser();


    /**
     * @return mixed
     */
    public function getChangeSet();

    /**
     * @return mixed
     */
    public function getClassName();


    /**
     * @return string
     */
    public function getClassId();


    /**
     * @return \DateTime
     */
    public function getDateTime();

}