<?php

/**
 * Allow disabling global helper functions
 */
if (defined('CODELIGHT_ACF_BLOCKS_DISALLOW_HELPERS') && CODELIGHT_ACF_BLOCKS_DISALLOW_HELPERS) {
    return;
}

/**
 * Getter method in global namespace for easier access.
 *
 * @return \Codelight\ACFBlocks\Blocks
 */
if (!function_exists('blocks')) {
    function blocks() {
        return function_exists('app') ? app(\Codelight\ACFBlocks\Blocks::class) : \Codelight\ACFBlocks\Blocks::getInstance();
    }
}

