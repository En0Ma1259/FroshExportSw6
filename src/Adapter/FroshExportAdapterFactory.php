<?php declare(strict_types=1);

namespace Frosh\Exporter\Adapter;

use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use Shopware\Core\Framework\Adapter\Filesystem\Adapter\AdapterFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FroshExportAdapterFactory implements AdapterFactoryInterface
{
    public function create(array $config): AdapterInterface
    {
        $options = $this->resolveOptions($config);

        return new Local(
            $options['root'],
            \LOCK_EX | FILE_APPEND,
            Local::DISALLOW_LINKS,
            $options
        );
    }

    public function getType(): string
    {
        return 'frosh';
    }

    // Copy LocalFactory
    private function resolveOptions(array $config): array
    {
        $options = new OptionsResolver();

        $options->setRequired(['root']);
        $options->setDefined(['file', 'dir', 'url']);

        $options->setAllowedTypes('root', 'string');
        $options->setAllowedTypes('file', 'array');
        $options->setAllowedTypes('dir', 'array');

        $options->setDefault('file', []);
        $options->setDefault('dir', []);

        $config         = $options->resolve($config);
        $config['file'] = $this->resolveFilePermissions($config['file']);
        $config['dir']  = $this->resolveDirectoryPermissions($config['dir']);

        return $config;
    }

    // Copy LocalFactory
    private function resolveFilePermissions(array $permissions): array
    {
        $options = new OptionsResolver();

        $options->setDefined(['public', 'private']);

        $options->setAllowedTypes('public', 'int');
        $options->setAllowedTypes('private', 'int');

        $options->setDefault('public', 0666 & ~umask());
        $options->setDefault('private', 0600 & ~umask());

        return $options->resolve($permissions);
    }

    // Copy LocalFactory
    private function resolveDirectoryPermissions(array $permissions): array
    {
        $options = new OptionsResolver();

        $options->setDefined(['public', 'private']);

        $options->setAllowedTypes('public', 'int');
        $options->setAllowedTypes('private', 'int');

        $options->setDefault('public', 0777 & ~umask());
        $options->setDefault('private', 0700 & ~umask());

        return $options->resolve($permissions);
    }
}
