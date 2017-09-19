<?php

namespace Codelight\PageBuilder;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ContentBuilder
 * @package Codelight\ContentBuilder
 */
class ContentBuilder
{
    /* @var int|string */
    protected $postId;

    /* @var array */
    protected $blocks = [];

    /**
     * ContentBuilder constructor.
     *
     * @param $postId
     * @param array $blockTypes
     */
    public function __construct($postId, $blocks)
    {
        $this->postId = $postId;
        $this->blocks = $blocks;
    }

    /**
     * Get prepared block by name from the current builder
     *
     * @param $name
     * @return BlockInterface
     */
    public function getBlock($name)
    {
        return $this->blocks[$name];
    }

    /**
     * Get prepared blocks from current builder
     *
     * @return array
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * Get array of rendered blocks
     *
     * @return array
     */
    public function renderBlocks()
    {
        $blocks = [];

        if (count($this->blocks)) {
            foreach ($this->blocks as $name => $block) {
                $blocks[$name] = $this->renderBlock($name);
            }
        }

        return $blocks;
    }

    /**
     * Render one of the current builder's blocks by name
     *
     * @param $name
     * @param null $template
     * @return string
     */
    public function renderBlock($name, $template = null)
    {
        $block = $this->getBlock($name);

        if (!$template) {
            $template = $block->getTemplate();
        }

        // Allow overriding the render method in BlockType class
        if (method_exists($block->getBlockType(), 'render')) {
            return $block->getBlockType()->render();
        }

        return \App\template(
            $template,
            [
                'data'  => $block->getData(),
                'block' => $block,
            ]
        );
    }
}
