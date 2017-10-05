<?php

namespace Codelight\ACFBlocks;

if (!defined('ABSPATH')) {
    exit;
}


trait BlockTypeRegistryTrait
{
    /* @var BlockTypeRegistry */
    protected $blockTypeRegistry;

    /**
     * @param $blocks
     */
    public function registerBlockTypes($blocks)
    {
        $this->blockTypeRegistry->registerBlockTypes($blocks);
    }

    /**
     * @param $block
     */
    public function registerBlockType($block)
    {
        $this->blockTypeRegistry->registerBlockType($block);
    }

    /**
     * @return array
     */
    public function getBlockTypes()
    {
        return $this->blockTypeRegistry->getBlockTypes();
    }

    /**
     * @param $name
     * @return BlockTypeInterface
     */
    public function getBlockType($name)
    {
        return $this->blockTypeRegistry->getBlockType($name);
    }
}
