<?php

namespace Codelight\ACFBlocks;

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

    /* @var array */
    protected $renderedBlocks = [];

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
        if (isset($this->blocks[$name])) {
            return $this->blocks[$name];
        }

        return null;
    }

    /**
     * Get prepared blocks
     *
     * @return array
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * Get a prepared and rendered block block by name
     *
     * @param $name
     *
     * @return mixed|null
     */
    public function getRenderedBlock($name)
    {
        if (empty($this->renderedBlocks)) {
            $this->renderBlocks();
        }

        if (isset($this->renderedBlocks[$name])) {
            return $this->renderedBlocks[$name];
        }

        return null;
    }

    /**
     * Get all prepared and rendered blocks
     *
     * @return array
     */
    public function getRenderedBlocks()
    {
        if (empty($this->renderedBlocks)) {
            $this->renderBlocks();
        }

        return $this->renderedBlocks;
    }

    /**
     * Get array of rendered blocks
     *
     * @return array
     */
    public function renderBlocks()
    {
        if (count($this->blocks)) {
            foreach ($this->blocks as $name => $block) {
                $this->renderedBlocks[$name] = $this->renderBlock($name);
            }
        }
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

        if (!$block) {
            return false;
        }

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
