<?php

namespace Codelight\ACFBlocks;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class FlexibleContentBlockType
 * @package Codelight\PageBuilder
 */
class FlexibleContentBlockType extends BlockType
{
    use BlockTypeRegistryTrait;

    /* @var BlockTypeRegistry */
    protected $blockTypeRegistry;

    /**
     * Set up the main flexible content field,
     * add callback for rendering the blocks and returning as data
     */
    protected function setup()
    {
        $this->blockTypeRegistry = new BlockTypeRegistry();
        $this->setupFlexibleContent();
        $this->addCallback([$this, 'renderRegisteredBlocks']);

        parent::setup();
    }

    /**
     * Set up the main ACF flexible content field
     */
    protected function setupFlexibleContent()
    {
        $this->getFieldsBuilder()
            ->addFlexibleContent($this->getName())
            ->endFlexibleContent();
    }

    /**
     * Register a block type as ACF Flexible Content layout
     *
     * @param mixed $blockType
     */
    public function registerBlockType($blockType)
    {
        if (is_string($blockType)) {
            $blockType = new $blockType();
        }

        $this->getFieldsBuilder()
            ->getField($this->getName())
            ->addLayout($blockType->getName())
            ->addFields($blockType->getFieldsBuilder());

        $this->blockTypeRegistry->registerBlockType($blockType);
    }

    /**
     * Render the registered blocks
     *
     * @param $data
     * @return array|string
     */
    public function renderRegisteredBlocks($data)
    {
        /**
         * Fetch the data of the main flexible content field.
         * It should be an array of flexible layouts structured like this:
         *
         * [
         *   'acf_fc_layout'        => 'layout_name',
         *   'content_field_name_1' => 'some value',
         *   'content_field_name_2' => 'some other value',
         *   // etc
         * ]
         */
        $flexibleContentData = $data[$this->getName()];

        if (empty($flexibleContentData)) {
            return '';
        }

        $blocks = [];
        // For every item in the layout
        foreach ($flexibleContentData as $layout) {
            // Get the block type object
            $blockType = $this->getBlockType($layout['acf_fc_layout']);

            // Check if the block type exists, i.e. that this is a valid FC Layout
            // (Old, not cleaned up layouts might still exist in the database)
            if (!$blockType) {
                // todo: debug mode
                if (false) {
                    trigger_error(
                        "Skipping flexible content layout {$layout['acf_fc_layout']} which does not have a valid BlockType.",
                        E_USER_NOTICE
                    );
                }
                continue;
            }

            // Create the block
            $block = $blockType->createBlock();
            // Set the data (which we already have from ACF)
            $block->setData($layout);
            // Add it to the list of blocks
            $blocks[$this->findUniqueIndex($blockType->getName(), $blocks)] = $block;
        }

        // Create a new Builder, inject the blocks
        $builder = new ContentBuilder($this->getName(), $blocks);
        // And let it render the blocks
        $data['blocks'] = $builder->getRenderedBlocks();

        return $data;
    }

    /**
     * Find a unique name for block in the flexible layout
     *
     * @param $name
     * @param $blocks
     * @param int $i
     * @return string
     */
    protected function findUniqueIndex($name, $blocks, $i = 2)
    {
        // No suffix for the first item
        if (!array_key_exists($name, $blocks)) {
            return $name;
        }

        // For the rest of the items, start count from 2
        // e.g. 'itemName-2'
        if (array_key_exists($name. '-' . $i, $blocks)) {
            $i++;
            return $this->findUniqueIndex($name, $blocks, $i);
        }

        return $name. '-' . $i;
    }
}
