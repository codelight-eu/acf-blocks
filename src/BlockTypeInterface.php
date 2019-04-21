<?php

namespace Codelight\ACFBlocks;

use StoutLogic\AcfBuilder\FieldsBuilder;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface BlockTypeInterface
 * @package Codelight\ContentBuilder
 */
interface BlockTypeInterface
{
    /**
     * @return Block
     */
    public function createBlock();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return FieldsBuilder
     */
    public function getFieldsBuilder();

    /**
     * @return string
     */
    public function getTemplate();

    /**
     * @param $template
     * @return void
     */
    public function setTemplate($template);

    /**
     * @param $callback
     * @return void
     */
    public function addCallback(callable $callback);

    /**
     * @return array
     */
    public function getCallbacks();

    /**
     * @param SettingInterface $setting
     * @return void
     */
    public function registerSetting(SettingInterface $setting);

    /**
     * @return array
     */
    public function getSettings();
}
