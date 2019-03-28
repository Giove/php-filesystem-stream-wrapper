# Filesystem stream wrapper

[![Build Status](https://travis-ci.org/Giove/php-filesystem-stream-wrapper.svg?branch=master)](https://travis-ci.org/Giove/php-filesystem-stream-wrapper)

## Usage
```php
try {
    StreamRegistry::register('wrapper', '/real/directory');

    mkdir('wrapper://dir');
    touch('wrapper://dir/file.txt');

    file_put_contents('wrapper://dir/file.txt', 'test content');
    $content = file_get_contents('wrapper://dir/file.txt');

    var_dump($content); //string(12) "test content"

    var_dump(glob('/real/directory/dir/*'));
    /*
    array(1) {
      [0] => string(29) "/real/directory/dir/file.txt"
    }
    */

    StreamRegistry::unregister('wrapper');
} catch (WrapperException $e) {
    //internal handler wraps every notice/warning/error into WrapperException
    $previous = $e->getPrevious();

    throw $e;
}
```

```php
try {
    StreamRegistry::register('wrapper', '/real/directory');

    $writer = new \XMLWriter();
    $writer->openUri('wrapper://new-file');
    $writer->startDocument('1.0', 'UTF-8');
    $writer->startElement('lorem');
    $writer->writeRaw('ipsum');

    //flush xml-writer buffer
    $writer->flush();

    //filesystem handle used internally
    $fsHandle = StreamRegistry::getLastHandle('wrapper://new-file');

    //write something to xml file bypassing xml-writer
    fwrite($fsHandle, '-dolor');

    $writer->endElement();
    $writer->endDocument();
    $writer->flush();
    unset($writer);

    echo file_get_contents('/real/directory/new-file');
    /*
        <?xml version="1.0" encoding="UTF-8"?>
        <lorem>ipsum-dolor</lorem>
    */

    StreamRegistry::unregister('wrapper');
} catch (WrapperException $e) {
    //handle exception
}
```

## Install
```
composer require giove/filesystem-stream-wrapper
```

## License
Dual licensed under LGPL v2.1 or LGPL v3.0
