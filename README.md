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

### Anax-MVC (Get this one first)
* Github: https://github.com/mosbth/Anax-MVC
* Version: v2.0.x or later
* License: MIT license

### Mos/CDatabase
* Github: https://github.com/mosbth/cdatabase/
* Version: v0.1.1*
* License: MIT license

### Mos/CForm V2
* Github: https://github.com/mosbth/cform/tree/v2
* Version: v1.9.8
* License: MIT license

### Anax/CDatabaseModel (Only if you not got your own)
* Github: https://github.com/chibi86/CDatabaseModel
* Version: v1.0.1
* License: MIT license

Install instructions
--------------------

### 1. First you will need to install the other modules (if not got theim already). 

### 2. The easiest way to install this is using composer. Add this to your composer.json: 

```javascript
    "chp/textcontent": "dev-master"
```

### 3. Move `vendor\chp\textcontent\app\view` to `app\view` and `vendor\chp\textcontent\webroot` to `webroot`

### 4a. OWN FRONTCONTROLLER

Include this to your frontcontroller this controllers 

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

Don`t forget to config database settings for CDatabase in your frontcontroller etc.

### 4b. MINE FRONTCONTROLLER (content.php)

Set chmod 777 on `webroot/db` if you want to use sqlite, otherwish config mysql for CDatabase in frontcontroller etc.

### 5. Controllers has a url-prefix variable that probly you need to change if you use your own frontcontroller or .htaccess to redirect

### 6. Go to `[your-url]/[frontcontroller]/content/setup` to setup database TextContent tables to database. 


History
-----------------------------------

###History for Database TextContent for ANAX-MVC 

v1.1.3 (2017-03-11)

* Added test\config.php for travis
* Update: Readme

v1.1.2 (2017-03-11)

* Added .phpunit.xml for config phpunit

v1.1.1 (2017-03-09)

* Change: Travis-settings
* Add files for Travis

v1.1.0 (2017-03-09)

* Added phpunit testcases
* Bugfix: Added fallbacks if inputed values is not valid, for some functions
* Bugfix: Fix so sqlite working
* Bugfix: Fix filter validate

v1.0.1 (2016-11-20)

* Add sqlite prepare
* Add more to install instructions and correct somethings
* Add link to CDatabaseModel, no need more follow swedish guide...
* Remove prepare in frontcontroller for mysql-config file
* Bugfix: That made TextContent not work at all
* Bugfix: That made setup/restore fail

v1.0.0 (2016-11-19)

* First release on Github.



```
Copyright (c) 2016 Rasmus Berg, rasmus.berg@chibidesign.se
```
