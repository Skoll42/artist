<?php
declare(strict_types=1);

namespace Payment\StripeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('stripe');
        $rootNode
            ->children()
                ->scalarNode('stripe_private_api_key')
                    ->isRequired()
                    ->info('Your secret Stripe API key')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}