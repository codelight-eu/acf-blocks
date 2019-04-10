<?php

namespace Codelight\ACFBlocks\Settings;

use Codelight\ACFBlocks\Setting;
use Codelight\ACFBlocks\SettingInterface;

class Id extends Setting
{
    protected $name = 'id';

    public function init()
    {
        $this->getFieldsBuilder()
            ->addText('id', ['label' => 'ID', 'instructions' => 'Can be used to reference this block via links or buttons']);
    }
}
