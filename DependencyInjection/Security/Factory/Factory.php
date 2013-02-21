<?php

namespace Escape\WSSEAuthenticationBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

class Factory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.wsse.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('security.authentication.provider.wsse'))
            ->replaceArgument(0, new Reference($userProvider))
            ->replaceArgument(1, $config['nonce_dir'])
            ->replaceArgument(2, $config['lifetime']);

        $entryPointId = $this->createEntryPoint($container, $id, $config, $defaultEntryPoint);

        $listenerId = 'security.authentication.listener.wsse.'.$id;
        $container
            ->setDefinition($listenerId, new DefinitionDecorator('security.authentication.listener.wsse'))
            ->addArgument(new Reference($entryPointId));

        return array($providerId, $listenerId, $entryPointId);
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'wsse';
    }

    public function addConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('nonce_dir')->defaultValue(null)->end()
                ->scalarNode('lifetime')->defaultValue(300)->end()
                ->scalarNode('realm')->defaultValue(null)->end()
                ->scalarNode('profile')->defaultValue('UsernameToken')->end()
            ->end();
    }

    protected function createEntryPoint($container, $id, $config, $defaultEntryPoint)
    {
        if($defaultEntryPoint !== null)
        {
            return $defaultEntryPoint;
        }

        $entryPointId = 'security.authentication.entry_point.wsse.'.$id;

        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('security.authentication.entry_point.wsse'))
            ->addArgument($config['realm'])
            ->addArgument($config['profile']);

        return $entryPointId;
    }
}