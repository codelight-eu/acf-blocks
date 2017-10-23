<?php

namespace Codelight\ACFBlocks;

if (!defined('ABSPATH')) {
    exit;
}

use StoutLogic\AcfBuilder\FieldsBuilder;

/**
 * Class ACF
 * @package Codelight\PageBuilder
 */
class ACF
{
    /**
     * Register the field groups defined in the block type using acf_add_local_field_group()
     *
     * @param BlockTypeInterface $blockType
     */
    public function registerBlockTypeFields(BlockTypeInterface $blockType)
    {
        $fieldsBuilder = $blockType->getFieldsBuilder();

        if ($fieldsBuilder) {
            $fields = $fieldsBuilder->build();
            if ($fields) {
                acf_add_local_field_group($fields);
            }
        }
    }

    /**
     * Get the block type names which are registered to an object with the given ID
     *
     * @param $postId
     * @return array
     */
    public function getPostBlockTypeNames($postId)
    {
        $blockTypeNames = [];
        $fieldGroups    = acf_get_field_groups(['post_id' => $postId]);

        foreach ($fieldGroups as $fieldGroup) {
            // Remove ACF-s internal prefix
            $blockTypeNames[] = substr($fieldGroup['key'], 6);
        }

        return $blockTypeNames;
    }

    /**
     * Get all the ACF data of an object with the given ID
     *
     * @param $postId
     * @param FieldsBuilder $fieldsBuilder
     * @return array
     */
    public function getPostBlockData($postId, FieldsBuilder $fieldsBuilder)
    {
        // acf_get_field_groups() doesn't return the field names, so we'll need to build the
        // config again to actually get them
        $fieldGroup = $fieldsBuilder->build();

        $data = [];

        foreach ($fieldGroup['fields'] as $field) {
            $data[$field['name']] = get_field($field['key'], $postId);
        }

        return $data;
    }
}
