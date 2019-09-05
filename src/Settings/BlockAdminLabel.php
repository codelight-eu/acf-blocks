<?php

namespace Codelight\ACFBlocks\Settings;

use Codelight\ACFBlocks\Setting;

class BlockAdminLabel extends Setting
{
    protected $name = 'block_admin_label';

    public function init()
    {
        $this->getFieldsBuilder()
             ->addText('block_admin_label', ['label' => 'Admin Label', 'instructions' => 'Give this block a name. This is only displayed for administrators.']);
    }
}
