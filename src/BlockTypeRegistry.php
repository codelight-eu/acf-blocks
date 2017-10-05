<?php

namespace Codelight\ACFBlocks;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class BlockRegistry
 * @package Codelight\ContentBuilder
 */
class BlockTypeRegistry
{
    /* @var array */
    protected $blockTypes;

    /**
     * Get all registered blocks
     *
     * @return array
     */
    public function getBlockTypes()
    {
        return $this->blockTypes;
    }

    /**
     * Get a block by its unique name
     *
     * @param $name
     * @return BlockTypeInterface
     */
    public function getBlockType($name)
    {
        if (isset($this->blockTypes[$name])) {
            return $this->blockTypes[$name];
        }

        return null;
    }

    /**
     * Register an array of blocks
     *
     * @param array $blocks
     */
    public function registerBlockTypes(array $blockTypes)
    {
        if (count($blockTypes)) {
            foreach ($blockTypes as $blockType) {
                $this->registerBlockType($blockType);
            }
        }
    }

    /**
     * Register a single block
     *
     * @param $block
     * @param string $name
     */
    public function registerBlockType($blockType)
    {
        if ($blockType instanceof BlockTypeInterface) {
            if (isset($this->blockTypes[$blockType->getName()])) {
                trigger_error("A block with the name {$blockType->getName()} already exists!", E_USER_ERROR);
            }
            $this->blockTypes[$blockType->getName()] = $blockType;
        } else {
            trigger_error("Block does not implement BlockTypeInterface and will be ignored.", E_USER_WARNING);
        }
    }
}
