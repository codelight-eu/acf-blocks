<?php

namespace Codelight\ACFBlocks;

if (!defined('ABSPATH')) {
    exit;
}

use StoutLogic\AcfBuilder\FieldsBuilder;

/**
 * Class Block
 * @package Codelight\ContentBuilder
 */
class BlockType implements BlockTypeInterface
{
    /* @var string */
    protected $blockClass = 'Codelight\PageBuilder\Block';

    /* @var array */
    protected $config = [];

    /* @var array */
    protected $callbacks;

    /* @var FieldsBuilder */
    protected $fieldsBuilder;

    /**
     * todo: add support for DI
     *
     * BlockType constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config        = $this->config + $config;
        $this->fieldsBuilder = new FieldsBuilder($this->config['name']);

        $this->setup();
    }

    /**
     * Run setup functions
     */
    protected function setup()
    {
        // Pseudo-constructor for child classes
        if (method_exists($this, 'init')) {
            $this->init();
        }

        // Configure ACF fields
        if (method_exists($this, 'configureFields')) {
            $this->configureFields();
        }
    }

    /**
     * Initialize a new Block object
     *
     * @return BlockInterface
     */
    public function createBlock()
    {
        return new $this->blockClass($this);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->config['name'];
    }

    /**
     * @return FieldsBuilder
     */
    public function getFieldsBuilder()
    {
        return $this->fieldsBuilder;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        if (!isset($this->config['template']) || empty($this->config['template'])) {
            trigger_error("Template not configured for block type {$this->getName()}", E_USER_ERROR);
        }

        return $this->config['template'];
    }

    /**
     * @param $template
     */
    public function setTemplate($template)
    {
        $this->config['template'] = $template;
    }

    /**
     * @return array
     */
    public function getCallbacks()
    {
        return $this->callbacks;
    }

    /**
     * @param callable $callback
     */
    public function addCallback(callable $callback)
    {
        $this->callbacks[] = $callback;
    }
}
