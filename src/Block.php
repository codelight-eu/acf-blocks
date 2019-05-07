<?php

namespace Codelight\ACFBlocks;

if (!defined('ABSPATH')) {
    exit;
}

class Block implements BlockInterface
{
    /* @var BlockTypeInterface */
    protected $blockType;

    /* @var string */
    protected $id;

    /* @var string */
    protected $objectId;
    
    /* @var array */
    protected $data;

    /* @var array */
    protected $settings;

    /* @var array */
    protected $blocks;

    /* @var array */
    protected $rawData;

    /* @var array */
    protected $rawSettings;

    /* @var array */
    protected $meta;

    /**
     * Block constructor.
     * @param $blockType
     */
    public function __construct(BlockTypeInterface $blockType)
    {
        $this->blockType = $blockType;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getObjectId()
    {
        return $this->objectId;
    }

    public function setObjectId($id)
    {
        $this->objectId = $id;
    }

    /**
     * Return the block data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function setSettings($data, $settings)
    {
        // If any Settings have been registered, run the data through them
        if (count($this->blockType->getSettings())) {
            foreach ($this->blockType->getSettings() as $setting) {
                /* @var SettingInterface $setting */
                if (method_exists($setting, 'filterData')) {
                    $settings = $setting->filterData($data, $settings, $this->id, $this->objectId, $this->blocks);
                }
            }
        }

        $this->settings = $settings;
    }

    /**
     * Set the block data, passing it through any callbacks
     *
     * @param $data
     */
    public function setData($data, $settings)
    {
        // Add ID to the block's data
        // DEPRECATED: remove in v2
        if (!isset($data['block_id'])) {
            $data['block_id'] = $this->id;
        }
        
        // Pass data through registered callbacks
        // This allows comfortably overriding data if the block type is defined procedurally
        if (is_array($this->blockType->getCallbacks()) && count($this->blockType->getCallbacks())) {
            foreach ($this->blockType->getCallbacks() as $callback) {
                if (is_callable($callback)) {
                    $data = call_user_func($callback, $data, $settings, $this->id, $this->objectId, $this->blocks);
                } else {
                    trigger_error("A callback registered to {$this->getBlockTypeName()} is not callable.", E_USER_WARNING);
                }
            }
        }

        // If a data filtering function is defined, pass data through it
        // This allows comfortably overriding data if the block type is defined as a child class
        if (method_exists($this->blockType, 'filterData')) {
            $data = $this->blockType->filterData($data, $settings, $this->id, $this->objectId, $this->blocks);
        }

        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->blockType->getTemplate();
    }

    /**
     * @return string
     */
    public function getBlockTypeName()
    {
        return $this->blockType->getName();
    }

    /**
     * @return BlockTypeInterface
     */
    public function getBlockType()
    {
        return $this->blockType;
    }

    /**
     * @param $blocks
     */
    public function setBlocks(array $blocks)
    {
        $this->blocks = $blocks;
    }

    /**
     * @return array
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * @param array $data
     */
    public function setRawData(array $data)
    {
        $this->rawData = $data;
    }

    /**
     * @return array
     */
    public function getRawSettings()
    {
        return $this->rawSettings;
    }

    /**
     * @param array $settings
     */
    public function setRawSettings(array $settings)
    {
        $this->rawSettings = $settings;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getMeta($key)
    {
        return isset($this->meta[$key]) ? $this->meta[$key] : null;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setMeta($key, $value)
    {
        $this->meta[$key] = $value;
    }
}
