<?php

namespace AshleyDawson\DoctrineFlysystemBundle\Event;

/**
 * Class StorageEvents
 *
 * @package AshleyDawson\DoctrineFlysystemBundle\Event
 */
final class StorageEvents
{
    /**
     * Store events dispatch @see \AshleyDawson\DoctrineFlysystemBundle\Event\StoreEvent
     */
    const PRE_STORE = 'ashleydawson.doctrine_flysystem_bundle.pre_store';
    const POST_STORE = 'ashleydawson.doctrine_flysystem_bundle.post_store';
}