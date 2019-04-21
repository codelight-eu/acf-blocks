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
     * @param $id
     */
    public function setId($id);

    /**
     * @param $data
     * @return void
     */
    public function setData($data, $objectId);

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
