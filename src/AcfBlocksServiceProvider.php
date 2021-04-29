<?php

namespace Codelight\ACFBlocks;

use Codelight\Foundation\Abstracts\ServiceProvider;
use Roots\Sage\Container;

class AcfBlocksServiceProvider extends ServiceProvider
{
    public function __construct(\Illuminate\Contracts\Container\Container $container)
    {
        parent::__construct($container);
    }

    public $bindings = [
        BlockTypeRegistry::class,
        Blocks::class,
        Block::class,
        ACF::class
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
