<?php

namespace EightPoints\Bundle\GuzzleBundle\Tests\DependencyInjection\Compiler;

use EightPoints\Bundle\GuzzleBundle\DependencyInjection\Compiler\PluginCompilerPass;
use EightPoints\Bundle\GuzzleBundle\Plugin\GuzzlePluginExtensionInterface;
use GuzzleHttp\HandlerStack;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;

class PluginCompilerPassTest extends TestCase
{
    public function testProcessClientWithUndefinedPluginConfiguration()
    {
        $container = new ContainerBuilder();
        $container
            ->register('eight_points_guzzle.client.test_client')
            ->addTag(
                'eight_points_guzzle.guzzle_client',
                [
                    'name' => 'test_client',
                    'plugins' => json_encode(
                        [
                            'wsse' => [
                                'username' => 'mylogin',
                                'password' => 'mypwd',
                                'created_at' => '2018-10-16 08:00:00',
                            ]
                        ]
                    ),
                ]
            );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('There is no guzzle bundle plugin loaded with the name wsse');

        $pluginCompilerPass = new PluginCompilerPass();
        $pluginCompilerPass->process($container);
    }

    public function testProcessClientWithInValidPluginConfiguration()
    {
        $container = new ContainerBuilder();

        $handlerMock = new Definition(HandlerStack::class);
        $container->setDefinition('eight_points_guzzle.handler_stack.test_client', $handlerMock);

        //$handlerMock = $this->createMock(HandlerStack::class);
        $client = new Definition(
            null,
            [
                [
                    'handler' => $handlerMock
                ]
            ]
        );

        $extensionMock = $this->createMock(GuzzlePluginExtensionInterface::class);
        $extensionMock
            ->expects($this->once())
            ->method('getPluginName')
            ->willReturn('wsse');

        $extensionMock
            ->expects($this->once())
            ->method('registerClientPlugin')
            ->with(
                $container,
                $handlerMock,
                'test_client',
                [
                    'username' => 'mylogin',
                    'password' => 'mypwd',
                    'created_at' => '2018-10-16 08:00:00',
                ]
            )
            ->willThrowException(new \RuntimeException('Invalid configuration'));

        $container->registerExtension($extensionMock);

        $container
            ->setDefinition('eight_points_guzzle.client.test_client', $client)
            ->addTag(
                'eight_points_guzzle.guzzle_client',
                [
                    'name' => 'test_client',
                    'plugins' => json_encode(
                        [
                            'wsse' => [
                                'username' => 'mylogin',
                                'password' => 'mypwd',
                                'created_at' => '2018-10-16 08:00:00',
                            ]
                        ]
                    ),
                ]
            );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid configuration');

        $pluginCompilerPass = new PluginCompilerPass();
        $pluginCompilerPass->process($container);
    }

    public function testProcessClientWithValidPluginConfiguration()
    {
        $container = new ContainerBuilder();

        $handlerMock = new Definition(HandlerStack::class);
        $container->setDefinition('eight_points_guzzle.handler_stack.test_client', $handlerMock);

        //$handlerMock = $this->createMock(HandlerStack::class);
        $client = new Definition(
            null,
            [
                [
                    'handler' => $handlerMock
                ]
            ]
        );

        $extensionMock = $this->createMock(GuzzlePluginExtensionInterface::class);
        $extensionMock
            ->expects($this->once())
            ->method('getPluginName')
            ->willReturn('wsse');

        $extensionMock
            ->expects($this->once())
            ->method('registerClientPlugin')
            ->with(
                $container,
                $handlerMock,
                'test_client',
                [
                    'username' => 'mylogin',
                    'password' => 'mypwd',
                    'created_at' => '2018-10-16 08:00:00',
                ]
            )
            ->willReturn($handlerMock);

        $container->registerExtension($extensionMock);

        $container
            ->setDefinition('eight_points_guzzle.client.test_client', $client)
            ->addTag(
                'eight_points_guzzle.guzzle_client',
                [
                    'name' => 'test_client',
                    'plugins' => json_encode(
                        [
                            'wsse' => [
                                'username' => 'mylogin',
                                'password' => 'mypwd',
                                'created_at' => '2018-10-16 08:00:00',
                            ]
                        ]
                    ),
                ]
            );

        $pluginCompilerPass = new PluginCompilerPass();
        $pluginCompilerPass->process($container);
    }
}
