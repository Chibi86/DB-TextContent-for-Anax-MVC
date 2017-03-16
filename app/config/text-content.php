<?php
  define('CHP_TC_URLPREFIX', 'content.php/'); // Prefix on url
  define('CHP_TC_MINILENGTH', 3);             // Minimum length on text
  define('CHP_TC_POSTSPERPAGE', null);        // Max length per page on blog posts (null = unlimit)
  define('CHP_TC_CONTENTPERPAGE', null);      // Max length per page on content list (null = unlimit)
  define('CHP_TC_FILTERS', serialize(array(   // Existing filters-list, add if got more
    'bbcode','clickable','markdown', 'nl2br', 'shortcode'
  )));
  define('CHP_TC_TYPES', serialize(array(	    // Content types
    'blog-post'   => ['url' => 'blog/read/',  'field' => 'slug', 	'prefix' => '', 	'title' => 'Blog'],
    'page'        => ['url' => 'page/page/', 	'field' => 'url', 	'prefix' => '', 	'title' => 'Page']
  )));