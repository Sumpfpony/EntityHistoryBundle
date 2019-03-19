<?php
/**
 * Created by PhpStorm.
 * User: tonigurski
 * Date: 13.04.18
 * Time: 14:42
 */

namespace Sumpfpony\EntityHistoryBundle\StoreAdapter;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MonologStoreAdapter implements StoreAdapterInterface
{
    private $user = null;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;


    protected $manager;

    public function __construct(LoggerInterface $logger, TokenStorageInterface $tokenStorage, EntityManager $manager)
    {
        $this->manager = $manager;
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * logs info into the set upped default logger
     *
     * @param $className
     * @param $id
     * @param $changeSet
     */
    public function createLog($className, $id, $changeSet)
    {
        $this->logger->info('Entity Update ' . $className . '#' . $id . ' by ' . $this->getUser(), $changeSet);
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        if($this->user)
            return $this->user;

        return ($this->tokenStorage->getToken()) ? $this->tokenStorage->getToken()->getUsername() : 'system';
    }

    /**
     * @param string $user
     */
    public function setUser($user = ""): void
    {
        $this->user = $user;
    }

    /**
     * couz its a temporary store it will never return historic items
     *
     * @param string $className
     * @param int $id
     *
     * @return array
     */
    public function getHistories($className, $id)
    {
        return [];
    }


}
