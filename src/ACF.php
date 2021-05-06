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
     * Set up hooks
     */
    public function __construct()
    {
        add_filter('acf/fields/flexible_content/layout_title', [$this, 'renderLayoutAdminLabel'], 10, 4);
    }

    /**
     * If defined, render the admin label of each flexible layout.
     * This can be defined separately for each block in admin.
     *
     * @param $title
     * @param $field
     * @param $layout
     * @param $i
     * @return string
     */
    public function renderLayoutAdminLabel($title, $field, $layout, $i)
    {
        foreach($layout['sub_fields'] as $sub) {
            if ($sub['name'] == 'settings') {
                $key1 = $sub['key'];
                foreach ($sub['sub_fields'] as $sub2) {
                    $key2 = $sub2['key'];
                    if (is_array($field['value']) && array_key_exists($i, $field['value']) && stristr($key2, 'block_admin_label') && $value = $field['value'][$i][$key1][$key2]) {
                        return "{$title} - {$value}";
                    }
                }
            }
        }

        return $title;
    }

    /**
     * Register the field groups defined in the block type using acf_add_local_field_group()
     *
     * @param BlockTypeInterface $blockType
     */
    public function registerBlockTypeFields(BlockTypeInterface $blockType)
    {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }
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
    public function getPostFieldGroupNames($postId)
    {
        if ('option' === $postId) {
            return $this->getGlobalFieldGroupKeys();
        }

        $fieldGroupKeys = [];
        $fieldGroups    = acf_get_field_groups(['post_id' => $postId]);

        foreach ($fieldGroups as $fieldGroup) {
            // Remove ACF-s internal prefix
            $fieldGroupKeys[] = substr($fieldGroup['key'], 6);
        }

        return $fieldGroupKeys;
    }

    /**
     * Get names of all global field groups
     *
     * @return array
     */
    public function getGlobalFieldGroupKeys()
    {
        $blockTypeNames = [];
        $fieldGroups    = acf_get_field_groups();

        // Parse all Options Page field groups
        foreach ($fieldGroups as $fieldGroup) {
            if (isset($fieldGroup['location']) && count($fieldGroup['location'])) {
                foreach ($fieldGroup['location'] as $location) {
                    foreach ($location as $rule) {
                        if ('options_page' === $rule['param'] && in_array($rule['operator'], ['=', '=='])) {
                            // Remove ACF-s internal prefix
                            $blockTypeNames[] = substr($fieldGroup['key'], 6);
                        }
                    }
                }
            }
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

    public function getPostBlockSettings($postId, FieldsBuilder $fieldsBuilder, $groupName)
    {
        // acf_get_field_groups() doesn't return the field names, so we'll need to build the
        // config again to actually get them
        $fieldGroup = $fieldsBuilder->build();

        $settings = [];

        foreach ($fieldGroup['fields'] as $field) {
            if ($field['name'] === $groupName) {
                $settings = get_field($field['key'], $postId);
            }
        }

        return $settings;
    }
}
