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
    
    /* @var array */
    protected $data;

    /* @var array */
    protected $config;

    /**
     * Block constructor.
     * @param $blockType
     */
    public function __construct(BlockTypeInterface $blockType)
    {
        $this->blockType = $blockType;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
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

    /**
     * Set the block data, passing it through any callbacks
     *
     * @param $data
     */
    public function setData($data, $objectId)
    {
        // Add ID to the block's data
        $data['block_id'] = $this->id;

        // If any Settings have been registered, run the data through them
        if (count($this->blockType->getSettings())) {
            foreach ($this->blockType->getSettings() as $setting) {
                /* @var SettingInterface $setting */
                if (method_exists($setting, 'filterData')) {
                    $data = $setting->filterData($data, $objectId);
                }
            }
        }
        
        // Pass data through registered callbacks
        // This allows comfortably overriding data if the block type is defined procedurally
        if (is_array($this->blockType->getCallbacks()) && count($this->blockType->getCallbacks())) {
            foreach ($this->blockType->getCallbacks() as $callback) {
                if (is_callable($callback)) {
                    $data = call_user_func($callback, $data, $objectId);
                } else {
                    trigger_error("A callback registered to {$this->getBlockTypeName()} is not callable.", E_USER_WARNING);
                }
            }
        }

        // If a data filtering function is defined, pass data through it
        // This allows comfortably overriding data if the block type is defined as a child class
        if (method_exists($this->blockType, 'filterData')) {
            $data = $this->blockType->filterData($data, $objectId);
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
}
