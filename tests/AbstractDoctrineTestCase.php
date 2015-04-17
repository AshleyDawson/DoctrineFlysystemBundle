<?php

namespace AshleyDawson\DoctrineFlysystemBundle\Tests;

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Class AbstractDoctrineTestCase
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Tests
 */
abstract class AbstractDoctrineTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    private $entityManager = null;

    /**
     * Get entity manager
     *
     * @return EntityManager
     * @throws \Doctrine\ORM\ORMException
     */
    protected function getEntityManager()
    {
        if (null === $this->entityManager) {

            $entityManager = EntityManager::create(
                $this->getConnectionConfig(),
                $this->getEntityManagerConfig(),
                $this->getEventManager()
            );

            $schema = array_map(function ($class) use ($entityManager) {
                return $entityManager->getClassMetadata($class);
            }, (array) $this->getEntityClassNames());

            $schemaTool = new SchemaTool($entityManager);
            $schemaTool->dropSchema($schema);
            $schemaTool->createSchema($schema);

            $this->entityManager = $entityManager;
        }

        return $this->entityManager;
    }

    /**
     * Get entity manager configuration
     *
     * @return \Doctrine\ORM\Configuration
     * @throws \Doctrine\ORM\ORMException
     */
    protected function getEntityManagerConfig()
    {
        $config = new \Doctrine\ORM\Configuration();

        $config->setProxyDir(TESTS_TEMP_DIR . '/proxy');
        $config->setProxyNamespace('Proxy');
        $config->setAutoGenerateProxyClasses(true);
        $config->setClassMetadataFactoryName('Doctrine\ORM\Mapping\ClassMetadataFactory');
        $config->setMetadataDriverImpl(new AnnotationDriver($_ENV['annotation_reader']));
        $config->setDefaultRepositoryClassName('Doctrine\ORM\EntityRepository');
        $config->setQuoteStrategy(new DefaultQuoteStrategy());
        $config->setRepositoryFactory(new DefaultRepositoryFactory());

        return $config;
    }

    /**
     * Get connection configuration
     *
     * @return array
     */
    protected function getConnectionConfig()
    {
        return [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];
    }

    /**
     * Get event manager
     *
     * @return \Doctrine\Common\EventManager
     */
    protected function getEventManager()
    {
        return new EventManager();
    }

    /**
     * Get an array of entity class names that the entity
     * manager should operate on
     *
     * @return array
     */
    abstract protected function getEntityClassNames();
}