<?php
namespace AshleyDawson\DoctrineGaufretteStorableBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

/**
 * Class DoctrineFlysystemBundleExtension
 *
 * @package AshleyDawson\DoctrineGaufretteStorableBundle\DependencyInjection
 */
class DoctrineFlysystemBundleExtension extends Extension
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