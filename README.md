# page-builder

Example:

```
<?php

add_action('init', function() {

    $pageBuilder = BlockManager::getInstance();

    $flexible = new FlexibleContentBlockType(['name' => 'main_content']);
    $flexible->getFieldsBuilder()->setLocation('page_template', '==', 'views/products.blade.php');
    $flexible->setTemplate('partials.test.flexible');

    $test1 = new BlockType(['name' => 'test1']);
    $test1->getFieldsBuilder()
        ->addWysiwyg('content11')
        ->addText('content12');
    $test1->addCallback(function ($data) {
        $data['test'] = 'test1';
        return $data;
    });
    $test1->setTemplate('partials.test.test1');

    $test2 = new BlockType(['name' => 'test2']);
    $test2->getFieldsBuilder()
        ->addText('content21')
        ->addText('content22');
    $test2->addCallback(function ($data) {
        $data['test'] = 'test2';
        return $data;
    });
    $test2->setTemplate('partials.test.test2');

    $flexible->registerBlockType($test1);
    $flexible->registerBlockType($test2);

    $pageBuilder->registerBlockType($flexible);
});

```
