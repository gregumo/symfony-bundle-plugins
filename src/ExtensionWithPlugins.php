<?php

namespace Matthias\BundlePlugins;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Matthias\BundlePlugins\MainBundleService;

final class ExtensionWithPlugins extends Extension
{
    /**
     * @var string
     */
    private $alias;

    /**
     * @var BundlePlugin[]
     */
    private $registeredPlugins;

    /**
     * @var ConfigurationInterface
     */
    private $mainBundleConfiguration;

    /**
     * @var MainBundleService[]
     */
    private $mainBundleServices;

    /**
     * @param string $alias The alias for this extension (i.e. the configuration key)
     * @param array $registeredPlugins The plugins that were registered
     * @param null $mainBundleConfiguration
     * @param array $mainBundleServices
     */
    public function __construct($alias, array $registeredPlugins, $mainBundleConfiguration = null, array $mainBundleServices = array())
    {
        $this->registeredPlugins = $registeredPlugins;
        $this->alias = $alias;
        $this->mainBundleConfiguration = $mainBundleConfiguration;
        $this->mainBundleServices = $mainBundleServices;
    }

    /**
     * @inheritdoc
     */
    public function load(array $config, ContainerBuilder $container)
    {
        if($this->mainBundleConfiguration) {
            $this->processConfiguration($this->mainBundleConfiguration, $config);
        }

        foreach($this->mainBundleServices as $service) {
            $params = array($container, new FileLocator($service->getPath()));
            $reflection_class = new \ReflectionClass($service->getLoaderType());
            $loader = $reflection_class->newInstanceArgs($params);
            $loader->load($service->getFile());
        }

        $configuration = $this->getConfiguration($config, $container);

        $processedConfiguration = $this->processConfiguration($configuration, $config);

        foreach ($this->registeredPlugins as $plugin) {
            $this->loadPlugin($container, $plugin, $processedConfiguration);
        }
    }

    /**
     * @inheritdoc
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new ConfigurationWithPlugins($this->getAlias(), $this->registeredPlugins);
    }

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param ContainerBuilder $container
     * @param BundlePlugin $plugin
     * @param array $processedConfiguration The fully processed configuration values for this bundle
     */
    private function loadPlugin(ContainerBuilder $container, BundlePlugin $plugin, array $processedConfiguration)
    {
        $container->addClassResource(new \ReflectionClass(get_class($plugin)));

        $pluginConfiguration = $this->pluginConfiguration($plugin, $processedConfiguration);

        $plugin->load($pluginConfiguration, $container);
    }

    /**
     * Get just the part of the configuration values that applies to the given plugin.
     *
     * @param BundlePlugin $plugin
     * @param array $processedConfiguration The fully processed configuration values for this bundle
     * @return array
     */
    private function pluginConfiguration(BundlePlugin $plugin, array $processedConfiguration)
    {
        if (!isset($processedConfiguration[$plugin->name()])) {
            return array();
        }

        return $processedConfiguration[$plugin->name()];
    }
}
