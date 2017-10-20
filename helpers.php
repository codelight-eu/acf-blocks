<?php

/**
 * Getter method in global namespace for easier access.
 *
 * @return \Codelight\ACFBlocks\Blocks
 */
if (!function_exists('blocks')) {
    function blocks() {
        return \Codelight\ACFBlocks\Blocks::getInstance();
    }
}

