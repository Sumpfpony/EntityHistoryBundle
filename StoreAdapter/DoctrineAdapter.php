<?php


namespace Sumpfpony\EntityHistoryBundle\StoreAdapter;


use Doctrine\ORM\EntityManagerInterface;
use Sumpfpony\EntityHistoryBundle\Model\LogInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DoctrineAdapter implements StoreAdapterInterface
{
    private $user = null;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;


    /**
     * @var string
     */
    protected $entity;


    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;


    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }


    /**
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @param string $entity
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function setEntity(string $entity)
    {

        $reflect = new \ReflectionClass($entity);
        if (!$reflect->implementsInterface(LogInterface::class))
            throw new \Exception('can not set Log entity: the target class "' . $entity . '" does not implement ' . LogInterface::class);

        $this->entity = $entity;
    }

    /**
     * logs info into the set upped default logger
     *
     * @param $className
     * @param $id
     * @param $changeSet
     * @throws \Exception
     */
    public function createLog($className, $id, $changeSet)
    {

        if (is_null($this->entity))
            throw new \Exception('can not create log because no target class is defined');

        /**
         * @var LogInterface $log
         */

        $reflect = new \ReflectionClass($this->entity);
        $log = $reflect->newInstance();

        $log
            ->setClassId($id)
            ->setClassName($className)
            ->setChangeSet($changeSet)
            ->setDateTime(new \DateTime())
            ->setUser($this->getUser());

        $this->entityManager->persist($log);
        //$this->entityManager->getUnitOfWork()->computeChangeSet($this->entityManager->getClassMetadata($this->entity), $log);

        $this->entityManager->flush();


    }


    /**
     * @param string $className
     * @param int $id
     * @param int $limit
     * @param null $offset
     * @return LogInterface[]
     * @throws \Exception
     */
    public function getHistories($className, $id, $limit = 30, $offset = null)
    {

        if ($manager = $this->getEntityManager()) {
            return $manager->getRepository($this->entity)->findBy(['classId' => $id, 'className' => $className], null, $limit, $offset);
        } else {
            throw new \Exception('entitymanager misssing');
        }

    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        if ($this->user)
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


    public function getEntityManager()
    {
        return $this->entityManager;
    }


}