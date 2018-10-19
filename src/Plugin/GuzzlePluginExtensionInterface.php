<?php

namespace EightPoints\Bundle\GuzzleBundle\Plugin;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

interface GuzzlePluginExtensionInterface extends ExtensionInterface {

    /**
     * The name of this plugin. It will be used to match with configuration settings.
     *
     * @return string
     */
    public function getPluginName() : string;

    /**
     * @param ContainerBuilder $container
     * @param Definition $handler
     * @param string $clientName
     * @param array $options
     *
     * @throws \RuntimeException
     *
     * @return Definition
     */
    public function registerClientPlugin(ContainerBuilder $container, Definition $handler, string $clientName, array $options) : Definition;
}
