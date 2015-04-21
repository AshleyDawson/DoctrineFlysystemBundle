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

    /**
     * Delete events dispatch @see \AshleyDawson\DoctrineFlysystemBundle\Event\DeleteEvent
     */
    const PRE_DELETE = 'ashleydawson.doctrine_flysystem_bundle.pre_delete';
    const POST_DELETE = 'ashleydawson.doctrine_flysystem_bundle.post_delete';
}