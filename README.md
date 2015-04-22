Doctrine Flysystem Bundle
=========================

[![Build Status](https://travis-ci.org/AshleyDawson/DoctrineFlysystemBundle.svg?branch=develop)](https://travis-ci.org/AshleyDawson/DoctrineFlysystemBundle)

Add a flysystem storage behaviour to Doctrine entities in Symfony 2

Requirements
------------

```
 >= PHP 5.4
 >= Symfony Framework 2.3
```

Doctrine Support
----------------

* Support for Doctrine ORM - Complete
* Support for Doctrine ODM - Incomplete

Introduction
------------

I built this bundle to extend [flysystem](https://github.com/thephpleague/flysystem) filesystem abstraction. In fact, this library extends the [FlysystemBundle](https://github.com/1up-lab/OneupFlysystemBundle) for Symfony 2.

This bundle implements an "uploaded file" handler on [Doctrine](http://www.doctrine-project.org/) entities, allowing Flysystem to store the file as a part of the Doctrine entity lifecycle.

The first class citizen on the bundle is a **trait** that is applied to any Doctrine entity to give the Flysystem storage handler the ability to persist file details along with the entity.

Installation
------------

You can install the Doctrine Flysystem Bundle via Composer. To do that, simply require the package in your composer.json file like so:

```json
{
    "require": {
        "ashleydawson/doctrine-flysystem-bundle": "0.8.*"
    }
}
```

Run composer update to install the package. Then you'll need to register the bundle in your `app/AppKernel.php`:

```php
$bundles = array(
    // ...
    new Oneup\FlysystemBundle\OneupFlysystemBundle(), // Doctrine Flysystem Bundle depends on this
    new AshleyDawson\DoctrineFlysystemBundle\AshleyDawsonDoctrineFlysystemBundle(),
);
```

Configuration
-------------

Next, you'll need to configure at least one filesystem to store your files in. I'll lay out an example below, however, a better example of this can be found in the [FlysystemBundle documentation](https://github.com/1up-lab/OneupFlysystemBundle/blob/master/Resources/doc/filesystem_create.md#use-the-mount-manager).

```yaml
# app/config/config.yml
oneup_flysystem:
    adapters:
        my_adapter:
            local:
                directory: %kernel.root_dir%/cache

    filesystems:
        my_filesystem:
            adapter: my_adapter
            mount: my_filesystem_mount_name
```

**Note:** The line `mount: my_filesystem_mount_name` is important as this bundle references filesystems using their mount prefix defined here

Usage
-----

In order to use this bundle, you must apply the given trait to the entities you'd like to have carry an uploaded file.

```php
<?php

namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AshleyDawson\DoctrineFlysystemBundle\ORM\StorableTrait;

/**
 * Post
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Post
{
    /**
     * Use the storable file trait
     */
    use StorableTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Post
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the Flysystem filesystem mount prefix as
     * configured in https://github.com/1up-lab/OneupFlysystemBundle/blob/master/Resources/doc/filesystem_create.md#use-the-mount-manager
     *
     * <code>
     * // A single filesystem...
     *
     * public function getFilesystemMountPrefix()
     * {
     *     return 'example_filesystem_mount_prefix';
     * }
     *
     * // Or a list of filesystems...
     *
     * public function getFilesystemMountPrefix()
     * {
     *     return [
     *         'example_filesystem_mount_prefix_01',
     *         'example_filesystem_mount_prefix_02',
     *     ];
     * }
     * </code>
     *
     * @return string|array
     */
    public function getFilesystemMountPrefix()
    {
        return 'my_filesystem_mount_name'; // This is the mount prefix configured in app/config/config.yml
    }
}
```

The trait will add four fields to the entity:

* **file_name** : string
    * The original name of the file as uploaded by the client
    * E.g. foobar.gif
* **file_storage_path** : string
    * The storage path of the file. Defaults to the file name (above)
    * E.g. /path/to/foobar.gif
* **file_mime_type** : string
    * The resolved mime type of the file uploaded by the client
    * E.g. image/gif
* **file_size** : integer
    * The file size in bytes
    * E.g. 2324

You'll need to update your schema before using this entity.

```
app/console doctrine:schema:update [--force | --dump-sql]
```

The `getFilesystemMountPrefix()` abstract method defines the Flysystem mount prefix where you'd like the file associated with this entity to be stored defined in `app/config/config.yml`.

Form Type
---------

An example of using the entity with a form type

```php
<?php

namespace Acme\DemoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class PostType
 *
 * @package Acme\DemoBundle\Form
 */
class PostType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('uploaded_file', 'file', [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'Acme\DemoBundle\Entity\Post',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'post';
    }
}
```

Note: the field named "uploaded_file" maps to a parameter within the `AshleyDawson\DoctrineGaufretteStorableBundle\Model\UploadedFileTrait`. If you'd like to change this, simply add an accessor to your entity to act as a proxy:

```php
<?php

namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AshleyDawson\DoctrineGaufretteStorableBundle\Model\UploadedFileTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Post
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Post
{
    /**
     * Use the uploaded file trait
     */
    use UploadedFileTrait;

    // ...

    /**
     * Set my file
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @return $this
     */
    public function setMyFile(UploadedFile $file = null)
    {
        $this->setUploadedFile($file);

        return $this;
    }
}
```

Then you can add the new name to the form type, like so:

```php
    // ...

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('my_file', 'file', [
                'required' => false,
            ])
        ;
    }

    // ...
```

Events
------

The storage handler, which is a part of the Doctrine entity lifecycle, fires several events on the margins of the file storage activity. These are:

* **ashleydawson.doctrine_flysystem_bundle.pre_store**
    * Dispatched before file is written to filesystem
* **ashleydawson.doctrine_flysystem_bundle.post_store**
    * Dispatched after file is written to filesystem
* **ashleydawson.doctrine_flysystem_bundle.pre_delete**
    * Dispatched before file is deleted from filesystem
* **ashleydawson.doctrine_flysystem_bundle.post_delete**
    * Dispatched after file is deleted from filesystem

These events can be found within the namespace `AshleyDawson\DoctrineFlysystemBundle\Event\StorageEvents`.

A good use case for these events is if you want to change any details of the form before it is written, for example (inside a Symfony controller):

```php
// Replace the file storage path with a random md5 hash directory structure, name and file extension
$this->get('event_dispatcher')->addListener(StorageEvents::PRE_WRITE, function (StoreEvent $event) {

    // Build a directory structure like "af/9e"
    $fileStoragePath = implode('/', str_split(substr(md5(mt_rand()), 0, 4), 2));
    $event->setFileStoragePath(sprintf('/%s/%s.%s', $fileStoragePath, md5(mt_rand()), $event->getFileExtension()));

});
```

Of course, this is a crude example - but it does show how a file (or meta information about a file) may be changed. In the example above, I'm building a hash directory structure for the storage path. Something like this:

```
/af/9e/2997f54d953111d222c00a0b6ed94a50.gif
```

**Note:** please don't use the example above as a production solution as there is a chance of filename collision.

It may also be a good idea to mount a subscriber instead of doing a closure-based implementation as I've done above. You should always aim to deliver a system that promotes the single responsibility principal!

