<?php


namespace Codelight\ACFBlocks;

abstract class BlocksLoader
{
    /* @var array */
    protected $blockTypes = [];

    /* @var string */
    protected $blockNamespace = '\\';

    /* @var string */
    protected $googleApiKey = '';

    public function __construct()
    {
        add_action('acf/init', [$this, 'actionAcfInit']);
    }

    public function actionAcfInit()
    {
        if ($this->googleApiKey) {
            $this->updateGoogleApiKeySetting($this->googleApiKey);
        }

        if ($this->blockTypes && $this->blockNamespace) {
            $this->setupBlocks([
                'blocktypes' => $this->blockTypes,
                'namespace'  => $this->blockNamespace,
            ]);
        }
    }

    public function updateGoogleApiKeySetting($value)
    {
        acf_update_setting('google_api_key', $value);
    }

    public function setupBlocks($blocks)
    {
        $blocks = Blocks::getInstance();
        $blocks->init($blocks);
    }
}
