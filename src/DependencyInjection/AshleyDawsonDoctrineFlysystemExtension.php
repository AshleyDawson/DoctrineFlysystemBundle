<?php
namespace AshleyDawson\DoctrineFlysystemBundle\DependencyInjection;

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
        // ...

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}