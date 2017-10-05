# ACF Blocks
Make WordPress theme development great again!

## Overview
ACF Blocks is a lightweight library that provides a clean, object-oriented API to create and re-use ACF field groups and flexible layouts via objects we call *blocks*. ACF Blocks supports both quick and simple procedural block creation for small, generic projects as well as a class-based approach for more complex systems. 

Fields are created using the excellent [ACF Builder](https://github.com/StoutLogic/acf-builder) library.

### Example
As an example, let's build and render the field group of a simple event.

![Example 1](https://codelight.eu/acf-blocks/acf-blocks-example-1.png)

In this case, it makes sense for the events to be a custom post type. The ACF fields required are:
1. Image (image)
2. Start date (date picker)
3. End date (date picker)
4. Description (wysiwyg)

For the event title, we'll use the post title.

```php
<?php

use Codelight\ACFBlocks\Blocks;
use Codelight\ACFBlocks\BlockType;

add_action('init', function() {

    $twoColumnContent = new BlockType('two-column-content');
    

    $blocks = Blocks::getInstance();
    
    

});

```


## Installation
### Development
In your theme's composer.json file, add the following:
```
"repositories": [
    {
      "url": "git@github.com:codelight-eu/acf-blocks.git",
      "type": "git"
    }
],
"require": {
    "codelight/acf-blocks": "dev-master"
}
```
### Production
```
composer require codelight/acf-blocks
```

## Examples
Procedural example:

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

Class-based example:
```
<?php

add_action('init', function () {

    $pageBuilder = BlockManager::getInstance();
    
    $pageBuilder->init([
        'blocktypes' => [
            'Products\SimCards',
        ],
        'namespace'   => '\Codelight\Travelsim\Blocks\\',
    ]);
    
});
```

And the actual class:
```
<?php

namespace Codelight\Travelsim\Blocks\Products;

use Codelight\PageBuilder\BlockType;

class SimCards extends BlockType
{
    protected $config = [
        'name'     => 'sim_cards',
        'template' => 'partials.blocks.products-page.sim-cards',
    ];

    public function configureFields()
    {
        $this->fieldsBuilder
            ->addTab('travelsim_by_zones')
            ->addFlexibleContent('features_for_travelsim_zones')
            ->addLayout('feature')
            ->addWysiwyg('content')
            ->addImage('image')
            ->endFlexibleContent()
            ->addTab('travelsim_worldwide')
            ->addFlexibleContent('features_for_travelsim_worldwide')
            ->addLayout('feature')
            ->addWysiwyg('content')
            ->addImage('image')
            ->endFlexibleContent()
            ->addTab('travelchat')
            ->addFlexibleContent('features_for_travelchat')
            ->addLayout('feature')
            ->addWysiwyg('content')
            ->addImage('image')
            ->endFlexibleContent()
            ->setLocation('page_template', '==', 'views/products.blade.php');
    }

    public function filterData($data)
    {
        $data['test'] = 'yolo';
        return $data;
    }
}

```
