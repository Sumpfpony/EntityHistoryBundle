<?php


namespace Sumpfpony\EntityHistoryBundle\Model;

class BaseLog implements LogInterface
{

    /**
     * @var string
     */
    protected $user;

    /**
     * @var array
     */
    protected $changeSet;


    /**
     * @var string
     */
    protected $className;


    /**
     * @var string
     */
    protected $classId;


    /**
     * @var \DateTime
     */
    protected $dateTime;


    /**
     * @return array|null
     */
    public function getChangeSet()
    {
        return $this->changeSet;
    }


    /**
     * @param array $changeSet
     * @return LogInterface
     */
    public function setChangeSet(array $changeSet)
    {
        $this->changeSet = $changeSet;
        return $this;
    }


    /**
     * @return null|string
     */
    public function getClassName()
    {
        return $this->className;
    }


    /**
     * @param string $className
     * @return LogInterface
     */
    public function setClassName(string $className)
    {
        $this->className = $className;
        return $this;
    }


    /**
     * @return null|string
     */
    public function getClassId()
    {
        return $this->classId;
    }


    /**
     * @param string $classId
     * @return LogInterface
     */
    public function setClassId(string $classId)
    {
        $this->classId = $classId;
        return $this;
    }


    /**
     * @return \DateTime|null
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @param mixed $dateTime
     * @return LogInterface
     */
    public function setDateTime(\DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
        return $this;
    }


    /**
     * @param string $user
     * @return LogInterface
     */
    public function setUser(string $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getUser()
    {
        return $this->user;
    }
}
