DB TextContent for ANAX-MVC
===========================

[![Build Status](https://scrutinizer-ci.com/g/Chibi86/DB-TextContent-for-Anax-MVC/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Chibi86/DB-TextContent-for-Anax-MVC/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Chibi86/DB-TextContent-for-Anax-MVC/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Chibi86/DB-TextContent-for-Anax-MVC/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Chibi86/DB-TextContent-for-Anax-MVC/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Chibi86/DB-TextContent-for-Anax-MVC/?branch=master)

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

### 1. First you will need to install the other modules (if you not got them already). 

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

### 5. Change config-file

You probly should change url-prefix and other settings in `app/config/text-content.php`.

### 6. Go to `[your-url]/[frontcontroller]/content/setup` to setup database TextContent tables to database. 


History
-----------------------------------

###History for Database TextContent for ANAX-MVC 

v1.3.1 (2017-03-16)

* Changes: Comments now follow more phpDocumentor standard with small letters etc
* Changes: Some corrections in the code
* Removed: Slugify on content url-field, user need to follow slugify standard anyway
* Bugfix: Update date on blog post and page now only show when updates has been made after published
* Bugfix: Now all url:s got prefix, before some has been missed

v1.3.0 (2017-03-16)

* Updated: Install guide (readme)
* Added: Config-file 'app/config/text-content.php'
* Added: Help class 'ValidContent' to validate and return valid information
* Added: Test-cases for 'ValidContent'
* Added: InternalServerException if $di is not a object
* Changes: Some Doc Comments to small letters so scrutinizer accept them.
* Removed: Some functions and rebuild in 'ContentController' to just call to 'ValidContent' functions
* Removed: Some test-cases for 'ContentController'
* And more...

v1.2.5 (2017-03-14 - 2017-03-15)

* Added: More testcases, and testcases for Blog and Page controllers
* Change: Clean up classes with smaller functions
* Change: Fix small issues scrutinizer has inform about
* Bugfix: Remove content now works
* Bugfix: Content with no tags, can now edits
* And more...

v1.2.0 (2017-03-12)

* Added: @Property  annotations in comments before classes
* Added: Badges
* Removed: Short php open tags
* Removed: Php end tags, where it's not needed

v1.1.1 to 1.1.7 (2017-03-11 - 2017-03-12)

* Added: .phpunit.xml for config phpunit
* Added: Files for Travis
* Added: CDatabaseModel to require in composer for Travis
* Added: Anax-MVC to be require in composer for Travis
* Update: Travis-settings
* Update: Remove short php open tags
* Update: Remove php end tags, where it's not needed
* Update: Remove unused code
* Update: Comment-fixes in ContentController.php
* Update: Add autoload for vendor in test/config.php
* Bugfix: Fix Travis phpunit test
* Bugfix: Fix right namespaces on class calls in test-classes
* Bugfix: Fix so null values is not accepted in class functions
* And more fixes...

v1.1.0 (2017-03-09)

* Added: Phpunit testcases
* Bugfix: Added fallbacks if inputed values is not valid, for some functions
* Bugfix: Fix so sqlite working
* Bugfix: Fix filter validate

v1.0.1 (2016-11-20)

* Added: Sqlite prepare
* Added: More to install instructions and correct somethings
* Added: Link to CDatabaseModel, no need more follow swedish guide...
* Removed: Prepare in frontcontroller for mysql-config file
* Bugfix: That made TextContent not work at all
* Bugfix: That made setup/restore fail

v1.0.0 (2016-11-19)

* First release on Github.



```
Copyright (c) 2016-2017 Rasmus Berg, rasmus.berg@chibidesign.se
```
