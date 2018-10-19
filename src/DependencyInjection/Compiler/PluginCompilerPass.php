<?php

namespace EightPoints\Bundle\GuzzleBundle\DependencyInjection\Compiler;

use EightPoints\Bundle\GuzzleBundle\Plugin\GuzzlePluginExtensionInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PluginCompilerPass implements CompilerPassInterface
{
    /**
     * We tag handlers with specific services to listen too.
     *
     * We get all event tagged services from the container.
     * We then go through each event, and look for the value eight_points_guzzle_bundle.
     * For each one we find, we check if the service key is set, and then
     * call setServiceName on each EventListener.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $guzzleClientsServices = $container->findTaggedServiceIds('eight_points_guzzle.guzzle_client');

        /** @var GuzzlePluginExtensionInterface[] $availablePlugins */
        $availablePlugins = [];
        foreach ($container->getExtensions() as $extension) {
            if ($extension instanceof GuzzlePluginExtensionInterface) {
                $availablePlugins[$extension->getPluginName()] = $extension;
            }
        }

        foreach ($guzzleClientsServices as $guzzleClientServiceId => $guzzleClientServiceTags) {

            // check for invalid configuration
            $guzzleClientTagConfig = $guzzleClientServiceTags[0];
            $clientName = $guzzleClientTagConfig['name'];
            $guzzleClientPluginsConfig = json_decode($guzzleClientTagConfig['plugins'], true);

            foreach (array_keys($guzzleClientPluginsConfig) as $pluginName) {
                if (!in_array($pluginName, array_keys($availablePlugins))) {
                    throw new \RuntimeException(
                        sprintf(
                            'There is no guzzle bundle plugin loaded with the name %s.',
                            $pluginName
                        )
                    );
                }
            }

            $guzzleClientServiceDefinition = $container->getDefinition($guzzleClientServiceId);
            $guzzleClientServiceOptions = $guzzleClientServiceDefinition->getArgument(0);

            // load plugins into client
            foreach ($guzzleClientPluginsConfig as $guzzleClientPluginName => $guzzleClientPluginConfig) {

                $guzzleClientServiceOptions['handler'] = $availablePlugins[$guzzleClientPluginName]->registerClientPlugin(
                    $container,
                    $guzzleClientServiceOptions['handler'],
                    $clientName,
                    $guzzleClientPluginConfig
                );
            }

            $guzzleClientServiceDefinition->setArgument(0, $guzzleClientServiceOptions);
        }
    }
}
