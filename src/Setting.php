<?php

namespace Codelight\ACFBlocks;

use StoutLogic\AcfBuilder\FieldsBuilder;

abstract class Setting implements SettingInterface
{
    /* @var string */
    protected $name;

    /* @var array */
    protected $config = [];

    /* @var FieldsBuilder */
    protected $fieldsBuilder;

    /**
     * Setting constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $this->config + $config;

        if ( ! $this->name) {
            $this->name = $this->config['name'];
        }

        $this->fieldsBuilder = new FieldsBuilder($this->name);

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
     * @return FieldsBuilder
     */
    public function getFieldsBuilder()
    {
        return $this->fieldsBuilder;
    }

    abstract function init();
}
