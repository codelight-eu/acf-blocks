<?php

namespace Codelight\ACFBlocks;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * todo: consider getting rid of BlockRegistryTrait
 * todo: maybe we don't need post ID in ContentBuilder, as the ID is only used inside Blocks to fetch data from ACF
 * todo: check dependencies (ACF version)
 */

/**
 * Class Blocks
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
            foreach ($config['blocktypes'] as &$blockType) {
                if (is_string($blockType)) {
                    $blockType = $config['namespace'] . $blockType;
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

        foreach ($blockTypeNames as $blockTypeName) {

            $blockType = $this->blockTypeRegistry->getBlockType($blockTypeName);

            // Handle field groups that are not created using ACF Blocks
            if (!$blockType) {
                continue;
            }

            $block     = $blockType->createBlock();

            $data = $this->acf->getPostBlockData($postId, $blockType->getFieldsBuilder());
            $block->setData($data);

            $blocks[$blockTypeName] = $block;
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
     * Fetch all rendered blocks
     *
     * @param $postId
     */
    public function get($postId = null)
    {
        $postId = $this->resolvePostId($postId);
        $builder = $this->getBuilder($postId);
        return $builder->getRenderedBlocks();
    }

    /**
     * Get populated content builder
     *
     * @param $postId
     * @return ContentBuilder
     */
    public function getBuilder($postId = null)
    {
        $postId = $this->resolvePostId($postId);

        if (!isset($this->builders[$postId])) {
            $this->builders[$postId] = $this->createContentBuilder($postId);
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
        // todo: sanity checks?

        if ($postId) {
            return $postId;
        }

        global $post;
        return $post->ID;
    }

    /**
     * Factory method to create a new ContentBuilder
     *
     * @param $postId
     * @param $blocks
     *
     * @return ContentBuilder
     */
    protected function createContentBuilder($postId)
    {
        $blocks = $this->getBlocksByPostId($postId);
        return new ContentBuilder($postId, $blocks);
    }

    /**
     * @param array $config
     *
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
