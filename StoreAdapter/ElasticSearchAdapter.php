<?php

namespace Sumpfpony\EntityHistoryBundle\StoreAdapter;


use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Sumpfpony\EntityHistoryBundle\Model\BaseLog;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ElasticSearchAdapter implements StoreAdapterInterface
{

    /**
     * @var string
     */
    protected $user;


    /**
     * @var array
     */
    protected $hosts = [];


    /**
     * @var string
     */
    protected $index = 'mm_entity_history';


    /**
     * @var string
     */
    protected $type = 'log';


    /**
     * @var Client|null
     */
    protected $client;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;


    /**
     * ElasticSearchAdapter constructor.
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }


    /**
     * @param string $className
     * @param int $id
     * @param $changeSet
     */
    public function createLog($className, $id, $changeSet)
    {
        $this->getClient()->index([
            'index' => $this->index,
            'type' => $this->type,
            'body' => [
                'className' => $className,
                'classId' => $id,
                'changeset' => serialize($changeSet),
                'user' => $this->getUser(),
                'dateTime' => new \DateTime()
            ]
        ]);
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
     * @param string $className
     * @param int $id
     * @return array|\Sumpfpony\EntityHistoryBundle\Model\LogInterface[]
     */
    public function getHistories($className, $id)
    {
        $elasticResult = $this->getClient()->search([
            'index' => $this->index,
            'type' => $this->type,
            //'size' => 0,
            'body' => [
                'query' => [
                    "bool" => [
                        "must" => [
                            ['match' => ['classId' => $id]],
                            ['match' => ['className' => $className]]
                        ]
                    ]
                ]
            ],
            'client' => [
                'timeout' => 1,
                'connect_timeout' => 1
            ]
        ]);

        $result = [];

        if(isset($elasticResult['hits']['hits'])) {

            $propertyAccess = new PropertyAccessor();

            foreach ($elasticResult['hits']['hits'] as $hit) {

                $changeSet = $propertyAccess->getValue($hit, '[_source][changeset]');
                if ($changeSet) $changeSet = unserialize($changeSet);

                $datetime = $propertyAccess->getValue($hit, '[_source][dateTime][date]');
                if ($datetime) {
                    $datetime = new \DateTime($datetime);

                    $log = new BaseLog();
                    $log
                        ->setUser($propertyAccess->getValue($hit, '[_source][user]'))
                        ->setClassName($propertyAccess->getValue($hit, '[_source][className]'))
                        ->setClassId($propertyAccess->getValue($hit, '[_source][classId]'))
                        ->setChangeSet($changeSet)
                        ->setDateTime($datetime);

                    $result[] = $log;
                }
            }
        }

        return $result;
    }


    /**
     * @param array $hosts
     * @return $this
     */
    public function setHosts(array $hosts)
    {
        $this->hosts = $hosts;
        return $this;
    }

    /**
     * @param string $index
     * @return $this
     */
    public function setIndex(string $index)
    {
        $this->index = $index;
        return $this;
    }


    /**
     * @return Client|null
     */
    protected function getClient()
    {
        if(!$this->client)
        {
            $clientBuilder = new ClientBuilder();
            $clientBuilder
                ->setHosts($this->hosts);

            $this->client = $clientBuilder->build();
        }

        return $this->client;
    }

}