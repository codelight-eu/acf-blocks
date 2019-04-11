<?php

namespace Codelight\ACFBlocks;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface BlockInterface
 * @package Codelight\PageBuilder
 */
interface BlockInterface
{
    /**
     * @return array
     */
    public function getData();

    /**
     * @return array
     */
    public function getSettings();

    /**
     * @param $data
     * @param $settings
     * @return void
     */
    public function setSettings(array $data, array $settings);

    /**
     * @param $data
     * @param $settings
     * @return void
     */
    public function setData(array $data, array $settings);

    /**
     * @return string
     */
    public function getTemplate();

    /**
     * @return BlockTypeInterface
     */
    public function getBlockType();

    /**
     * @return string
     */
    public function getBlockTypeName();
}
