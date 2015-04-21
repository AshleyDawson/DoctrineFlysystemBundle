<?php
namespace AshleyDawson\DoctrineFlysystemBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

/**
 * Class AshleyDawsonDoctrineFlysystemExtension
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\DependencyInjection
 */
class AshleyDawsonDoctrineFlysystemExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $config = $processor->processConfiguration($configuration, $config);

        $container->setParameter('ashley_dawson.doctrine_flysystem.can_delete_old_file',
            $config['delete_old_file_on_update']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}