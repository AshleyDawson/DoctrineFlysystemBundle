<?php

namespace AshleyDawson\DoctrineFlysystemBundle;

use AshleyDawson\MultiBundle\AbstractMultiBundle;

/**
 * Class AshleyDawsonDoctrineFlysystemBundle
 *
 * @package AshleyDawson\DoctrineFlysystemBundle
 */
class AshleyDawsonDoctrineFlysystemBundle extends AbstractMultiBundle
{
    /**
     * {@inheritdoc}
     */
    protected static function getBundles()
    {
        return [
            new \Oneup\FlysystemBundle\OneupFlysystemBundle(),
        ];
    }
}