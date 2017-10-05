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
     * @param $data
     * @return void
     */
    public function setData($data);

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
