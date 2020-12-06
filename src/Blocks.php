<?php

namespace Codelight\ACFBlocks;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * todo: consider getting rid of BlockRegistryTrait
 * todo: maybe we don't need post ID in ContentBuilder, as the ID is only used inside Blocks to fetch data from ACF
 * todo: check dependencies (ACF version)
 * todo: allow the user to set via config whether or not they want variables injected straight into the template or inside $data
 *
 * todo: bigger problem. Writing data into member variables of a BlockType instance is a shitty idea, but it's not immediately obvious
 */

/**
 * Class Blocks
 *
 * @package Codelight\ContentBuilder
 */
class Blocks
{
    use BlockTypeRegistryTrait;

    /* @var Blocks */
    protected static $instance;

    /* @var BlockTypeRegistry */
    protected $blockTypeRegistry;

    /* @var ACF */
    protected $acf;

    /* @var array */
    protected $builders = [];

    /**
     * Set this to true while in the process of building and rendering blocks.
     * This allows us to detect if a block is queried from inside another block
     * and avoid infinite loops.
     *
     * @var bool
     */
    protected $building = false;

    /**
     * ContentBuilder constructor
     */
    protected function __construct()
    {
        $this->acf               = new ACF();
        $this->blockTypeRegistry = new BlockTypeRegistry();
    }

    /**
     * Initialize the class by creating the block type objects and registering them
     *
     * @param array $config
     */
    public function init($config = [])
    {
        // Parse block types from given config
        $blockTypes = $this->parseConfig($config);

        // Register blocks from config, if applicable
        $this->registerBlockTypes($blockTypes);
    }

    /**
     * Parse the config array, automatically add namespaces if applicable
     *
     * @param $config
     * @return mixed
     */
    protected function parseConfig($config)
    {
        // If the key 'blocktypes' is not set, we assume that $config is just a simple array of block classes or objects
        if (!array_key_exists('blocktypes', $config)) {
            return $config;
        }

        // If both 'blocktypes' and 'namespace' keys are set, assume we have an array of non-namespaced classes
        // so let's add the namespace automatically
        if (array_key_exists('namespace', $config) && array_key_exists('blocktypes', $config)) {

            // Add trailing slash if it's missing
            $namespace = rtrim($config['namespace'], '\\') . '\\';

            foreach ($config['blocktypes'] as &$blockType) {
                if (is_string($blockType)) {
                    $blockType = $namespace . $blockType;
                }
            }
        }

        return $config['blocktypes'];
    }

    /**
     * Register an array of block types
     *
     * @param array $blockTypes
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
     * Register a single block type
     *
     * @param BlockTypeInterface $blockType
     */
    public function registerBlockType($blockType)
    {
        if (is_string($blockType)) {
            // Todo: add support for dependency injection
            $blockType = new $blockType();
        }

        // Register the block type in the main registry
        $this->blockTypeRegistry->registerBlockType($blockType);

        // Register the block type's ACF fields
        $this->acf->registerBlockTypeFields($blockType);
    }

    /**
     * Get and populate blocks by post id
     *
     * @param $postId
     * @return array
     */
    public function getBlocksByPostId($postId)
    {
        $blocks         = [];
        $blockTypeNames = $this->acf->getPostBlockTypeNames($postId);

        // Set up blocks
        foreach ($blockTypeNames as $blockTypeName) {

            $blockType = $this->blockTypeRegistry->getBlockType($blockTypeName);

            // Disregard field groups that are not created using ACF Blocks
            if (!$blockType) {
                continue;
            }

            $block = $blockType->createBlock();

            $block->setId($blockTypeName);
            $block->setObjectId($postId);

            $data     = $this->acf->getPostBlockData($postId, $blockType->getFieldsBuilder());
            $settings = $this->acf->getPostBlockSettings($postId, $blockType->getFieldsBuilder(), 'settings');

            $block->setRawData($data);
            $block->setRawSettings($settings);

            $blocks[$blockTypeName] = $block;
        }

        // On second loop, once each blocks has access to all the blocks' data in the current context.
        // This allows making decisions based on which specific block comes before or after the current.
        foreach ($blocks as $blockTypeName => $block) {
            /* @var $block Block */
            $block->setBlocks($blocks);
            $block->setSettings($block->getRawData(), $block->getRawSettings());
            $block->setData($block->getRawData(), $block->getRawSettings());
        }

        return $blocks;
    }

    /**
     * @param $postId
     * @param array $blockNames
     * @return array
     */
    public function getNamedBlocksByPostId($postId, array $blockNames)
    {
        $blocks = [];

        foreach ($blockNames as $blockTypeName) {
            $blockType = $this->blockTypeRegistry->getBlockType($blockTypeName);

            if (!$blockType) {
                continue;
            }

            $block = $blockType->createBlock();

            $block->setId($blockTypeName);
            $block->setObjectId($postId);

            $data     = $this->acf->getPostBlockData($postId, $blockType->getFieldsBuilder());
            $settings = $this->acf->getPostBlockSettings($postId, $blockType->getFieldsBuilder(), 'settings');

            $block->setRawData($data);
            $block->setRawSettings($settings);

            $blocks[$blockTypeName] = $block;
        }

        // On second loop, once each blocks has access to all the blocks' data in the current context.
        // This allows making decisions based on which specific block comes before or after the current.
        foreach ($blocks as $blockTypeName => $block) {
            /* @var $block Block */
            $block->setBlocks($blocks);
            $block->setSettings($block->getRawData(), $block->getRawSettings());
            $block->setData($block->getRawData(), $block->getRawSettings());
        }

        return $blocks;
    }

    /**
     * Get an instantiated block type object
     *
     * @param $blockTypeName
     * @return BlockTypeInterface
     */
    public function getBlockType($blockTypeName)
    {
        return $this->blockTypeRegistry->getBlockType($blockTypeName);
    }

    /**
     * Fetch all rendered blocks associated with a given Post
     *
     * @param $postId
     */
    public function get($postId = null)
    {
        $postId  = $this->resolvePostId($postId);
        $builder = $this->getBuilder($postId);
        return $builder->getRenderedBlocks();
    }

    /**
     * Fetch all block objects associated with a given Post without rendering them
     *
     * @param null $postId
     * @return array
     */
    public function getBlockObjects($postId = null)
    {
        $postId  = $this->resolvePostId($postId);
        $builder = $this->getBuilder($postId);
        return $builder->getBlocks();
    }

    /**
     * Fetch all rendered global blocks
     *
     * @return array
     */
    public function getGlobal()
    {
        $builder = $this->getBuilder('option');
        return $builder->getRenderedBlocks();
    }

    /**
     * Fetch all global block objects without rendering them
     *
     * @param null $postId
     * @return array
     */
    public function getGlobalBlockObjects()
    {
        $builder = $this->getBuilder('option');
        return $builder->getBlocks();
    }

    /**
     * Get a block by name
     *
     * @param      $name
     * @param null $postId
     *
     * @return null|BlockInterface
     */
    public function getByName($name, $postId = null)
    {
        $postId  = $this->resolvePostId($postId);
        $builder = $this->getBuilder($postId, $name);
        return $builder->getBlock($name);
    }

    /**
     * Get a rendered block by name
     *
     * @param      $name
     * @param null $postId
     *
     * @return array
     */
    public function getRenderedBlockByName($name, $postId = null)
    {
        $postId  = $this->resolvePostId($postId);
        $builder = $this->getBuilder($postId, $name);
        return $builder->getRenderedBlock($name);
    }

    /**
     * Get populated content builder
     *
     * @param $postId
     * @return ContentBuilder
     */
    public function getBuilder($postId = null, $blockName = null)
    {
        $postId = $this->resolvePostId($postId);

        if (!isset($this->builders[$postId])) {
            if (!$this->building || $postId === 'option') {
                $this->building = true;
                $this->builders[$postId] = $this->createContentBuilder($postId);
            } else {
                if (!$blockName) {
                    throw new \Exception('When rendering a block inside another block, you need to provide a block name.');
                }

                // Create a single-use content builder for the sole purpose of building this single block
                // without triggering an infinite loop.
                $this->building = false;
                return $this->createContentBuilder($postId, $blockName);
            }
        }

        return $this->builders[$postId];
    }

    /**
     * @param $postId
     *
     * @return int
     */
    protected function resolvePostId($postId)
    {
        $postId = apply_filters('codelight/acf_blocks/post_id', $postId);

        if ($postId) {
            return $postId;
        }

        if (is_home()) {
            return get_option('page_for_posts');
        }

        global $post;

        if ($post) {
            return $post->ID;
        }

        return null;
    }

    /**
     * Factory method to create a new ContentBuilder
     *
     * @param $postId
     * @param $blockName
     *
     * @return ContentBuilder
     */
    protected function createContentBuilder($postId, $blockName = null)
    {
        if ($blockName) {
            $blocks = $this->getNamedBlocksByPostId($postId, [$blockName]);
        } else {
            $blocks = $this->getBlocksByPostId($postId);
        }

        return new ContentBuilder($postId, $blocks);
    }

    /**
     * @return Blocks
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
