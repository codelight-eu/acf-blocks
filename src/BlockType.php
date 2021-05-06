<?php

namespace Codelight\ACFBlocks;

if ( ! defined('ABSPATH')) {
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
    protected $blockClass = 'Codelight\ACFBlocks\Block';

    /* @var string */
    protected $name;

    /* @var string */
    protected $fieldGroupName;

    /* @var array */
    protected $config = [];

    /* @var array */
    protected $callbacks;

    /* @var FieldsBuilder */
    protected $fieldsBuilder;

    /* @var array */
    protected $settings = [];

    /**
     * todo: add support for DI
     *
     * BlockType constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $this->config + $config;

        if ( ! $this->name) {
            $this->name = $this->config['name'];
        }

        $this->fieldsBuilder = new FieldsBuilder(isset($this->config['field_group_name'])?$this->config['field_group_name']:$this->name);

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

        // If any Settings have been defined, run them as well
        if (!count($this->settings)) {
            return;
        }

        $builder = $this->getFieldsBuilder();
        $builder = $builder->addGroup('settings', ['label' => __('Block Settings', 'acf-blocks'), 'wrapper' => ['width' => 100]]);
        foreach ($this->settings as $setting) {
            /* @var SettingInterface $setting */
            $builder = $builder->addFields($setting->getFieldsBuilder());
        }
        $builder = $builder->endGroup();
    }

    /**
     * Initialize a new Block object
     *
     * @return BlockInterface
     */
    public function createBlock()
    {
        if (method_exists($this, 'build')) {
            $this->build();
        }

        return new $this->blockClass($this);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
    * @return mixed
    */
    public function getTitle()
    {
        return $this->getFieldsBuilder()->getGroupConfig('title') ? $this->getFieldsBuilder()->getGroupConfig('title') : $this->getName();
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
        if ( ! isset($this->config['template']) || empty($this->config['template'])) {
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

    /**
     * @param callable $callback
     */
    public function removeCallback(callable $callback)
    {
        $index = array_search($callback, $this->callbacks);

        if ($index !== false) {
            unset($this->callbacks[$index]);
        } else {
            trigger_error("Attempting to remove a callback that doesn't exist.", E_USER_WARNING);
        }
    }

    /**
     * @param SettingsInterface $setting
     */
    public function registerSetting(SettingInterface $setting)
    {
        $this->settings[] = $setting;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param $id
     * @param $blocks
     * @return Block|null
     */
    public function getPreviousBlock($id, $blocks)
    {
        return $this->getBlockByRelativePosition(-1, $id, $blocks);
    }

    /**
     * @param $id
     * @param $blocks
     * @return Block|null
     */
    public function getCurrentBlock($id, $blocks)
    {
        return $this->getBlockByRelativePosition(0, $id, $blocks);
    }

    /**
     * @param $id
     * @param $blocks
     * @return Block|null
     */
    public function getNextBlock($id, $blocks)
    {
        return $this->getBlockByRelativePosition(1, $id, $blocks);
    }

    /**
     * @param $id
     * @param $blocks
     * @return Block|null
     */
    public function getBlockByRelativePosition($position, $id, $blocks)
    {
        $blockOrder = array_keys($blocks);
        $blockNames = array_flip($blockOrder);

        if (!isset($blockNames[$id])) {
            return false;
        }

        $currentBlockIndex = $blockNames[$id];
        $relativeIndex = $currentBlockIndex + $position;

        if (!isset($blockOrder[$relativeIndex])) {
            return false;
        }

        $relativeBlockName = $blockOrder[$relativeIndex];

        return isset($blocks[$relativeBlockName]) ? $blocks[$relativeBlockName] : null;
    }
}
