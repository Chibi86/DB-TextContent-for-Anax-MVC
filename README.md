DB TextContent for ANAX-MVC
===========================

This controller and modell classes adds possibly to add database administrat content to your Anax-MVC platform, like pages or blog posts.

By Rasmus Berg, rasmus.berg@chibidesign.se


License
------------------

This software is free software and carries a MIT license.


Use of external libraries
-----------------------------------

The following external modules are excluded byt will be needed for run this classes.

### Anax-MVC
* Github: https://github.com/mosbth/Anax-MVC
* Version: v2.0.x or later
* License: MIT license
* Setup: Get this one first

### Mos/CDatabase
* Github: https://github.com/mosbth/cdatabase/
* Version: v0.1.1*
* License: MIT license
* Setup: After installing you will need follow [this swedish instructions](https://dbwebb.se/kunskap/skapa-basklasser-for-databasdrivna-modeller-i-anax-mvc) (sorry) to build a base class for database modells in Anax-MVC.

### Mos/CForm V2
* Github: https://github.com/mosbth/cform/tree/v2
* Version: v1.9.8
* License: MIT license

Install instructions
--------------------

### 1. First you will need to install the other modules (if not got theim already). 

### 2. The easiest way to install this is using composer. Add this to your composer.json: 

```javascript
    "dahc/flashmessages": "dev-master"
```

### 3. Include to your frontcontroller this controllers

```php
$di->set('ContentController', function() use ($di) {
    $controller = new \Chp\TextContent\ContentController();
    $controller->setDI($di);
    return $controller;
});
$di->set('BlogController', function() use ($di) {
    $controller = new \Chp\TextContent\BlogController();
    $controller->setDI($di);
    return $controller;
});
$di->set('PageController', function() use ($di) {
    $controller = new \Chp\TextContent\PageController();
    $controller->setDI($di);
    return $controller;
});
```

### 4. Controllers has a url-prefix variable that probly you need to change from example-page if not using that one.

### 5. Go to `/content/setup` to setup database TextContent tables to database. 


History
-----------------------------------

###History for Database TextContent for ANAX-MVC 

v1.0.0 (2016-11-19)

* First release on Github.



```
Copyright (c) 2016 Rasmus Berg, rasmus.berg@chibidesign.se
```
