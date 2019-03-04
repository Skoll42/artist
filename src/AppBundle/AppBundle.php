<?php

namespace AppBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}
