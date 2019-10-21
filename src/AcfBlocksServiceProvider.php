<?php

namespace Codelight\ACFBlocks;

use Codelight\Foundation\Abstracts\ServiceProvider;
use Roots\Sage\Container;

class AcfBlocksServiceProvider extends ServiceProvider
{
    public $bindings = [
        Block::class,
        ACF::class,
        BlockTypeRegistry::class
    ];

    public $autoload = [];

    public $singletons = [
        Blocks::class,
    ];

    public function register()
    {
        $this->bindContentBuilder();
    }

    public function bindContentBuilder()
    {
        $this->container->bind(ContentBuilder::class, function(Container $container, array $params) {
            return new ContentBuilder($params['postId'], $params['blocks']);
        });
    }
}
