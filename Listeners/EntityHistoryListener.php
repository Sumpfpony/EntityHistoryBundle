<?php
/**
 * Created by PhpStorm.
 * User: tonigurski
 * Date: 26.10.17
 * Time: 13:25
 */

namespace Sumpfpony\EntityHistoryBundle\Listeners;

use Doctrine\ORM\EntityManager;
use  Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\PersistentCollection;
use Sumpfpony\EntityHistoryBundle\Registry\Catalogue;
use Sumpfpony\EntityHistoryBundle\StoreAdapter\StoreAdapterInterface;
use Doctrine\DBAL\Types\Type;

use Symfony\Component\HttpKernel\HttpKernelInterface;


class EntityHistoryListener
{
    /**
     * @var HttpKernelInterface
     */
    private $httpKernel;

    /**
     * @var StoreAdapterInterface
     */
    private $storeAdapter;
    /**
     * @var Catalogue
     */
    private $catalogue;

    /**
     * EntityHistoryListener constructor.
     * @param HttpKernelInterface $httpKernel
     * @param StoreAdapterInterface $storeAdapter
     * @param Catalogue $catalogue
     */
    function __construct(HttpKernelInterface $httpKernel, StoreAdapterInterface $storeAdapter, Catalogue $catalogue)
    {
        $this->httpKernel = $httpKernel;
        $this->storeAdapter = $storeAdapter;
        $this->catalogue = $catalogue;
    }

    /**
     * @param $entity
     * @return bool
     */
    private function isLoggableEntity($entity)
    {
        return $this->catalogue->isRegistered($entity);
    }

    private function value(EntityManager $em, Type $type, $value)
    {
        $platform = $em->getConnection()->getDatabasePlatform();
        switch ($type->getName()) {
            case Type::BOOLEAN:
                return $type->convertToPHPValue($value, $platform); // json supports boolean values
            default:
                return $type->convertToDatabaseValue($value, $platform);
        }
    }

    private function id(EntityManager $em, $entity)
    {
        $meta = $em->getClassMetadata(get_class($entity));
        $pk = $meta->getSingleIdentifierFieldName();
        $pk = $this->value(
            $em,
            Type::getType($meta->fieldMappings[$pk]['type']),
            $meta->getReflectionProperty($pk)->getValue($entity)
        );
        return $pk;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $doCompute = false;

        $inserted = [];
        $removed = [];

        $changeSets = [];

        /**
         * @var $collection PersistentCollection
         */
        foreach ($uow->getScheduledCollectionUpdates() as $collection) {
            $mapping = $collection->getMapping();
            if (!$mapping['isOwningSide'] || $mapping['type'] !== ClassMetadataInfo::MANY_TO_MANY) {
                continue;
                // ignore inverse side or one to many relations
            }
            $entity = $collection->getOwner();

            if ($this->isLoggableEntity($entity)) {

                $className = get_class($entity);

                if (!isset($changeSets[$className]))
                    $changeSets[$className] = [];

                $changeSets[$className][$entity->getId()] = [$mapping['fieldName'] => [
                    $collection->getSnapshot(),
                    $collection->toArray()

                ]];
            }
        }

        /**
         * check updates
         */
        foreach ($uow->getScheduledEntityUpdates() as $entity) {

            if ($this->isLoggableEntity($entity)) {

                $className = get_class($entity);
                $id = $entity->getId();

                if (!isset($changeSets[$className]))
                    $changeSets[$className] = [];

                $preChangeSet = isset($changeSets[$className][$id])?$changeSets[$className][$id]: null;

                $changeSet = $uow->getEntityChangeSet($entity);

                $changeSets[$className][$id] = is_array($preChangeSet) ? array_merge($preChangeSet, $changeSet) : $changeSet;
            }
        }

        foreach ($changeSets as $className => $entities)
            foreach ($entities as $id => $changeSet) {
                $this->saveLog($className, $id, $changeSet);

                $doCompute = true;
            }


        if ($doCompute)
            $uow->computeChangeSets();

    }

    /**
     * creates log with given storeAdapter
     *
     * @param $className
     * @param $id
     * @param $changeSet
     */
    private function saveLog($className, $id, $changeSet)
    {

        if ($className && $id > 0)
            $this->storeAdapter->createLog($className, $id, $changeSet);
    }
}