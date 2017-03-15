<?php
namespace Chp\TextContent;

/**
 * Text content controller
 * Made by Rasmus Berg (c) 2014-2017
 *
 * @Property  Object  $di         Anax-MVC class handler
 * @Property  Object  $request    Anax-MVC $_POST, $_GET and $_SERVER handler class
 * @Property  Object  $response   Anax-MVC Php Header class
 * @Property  Object  $url        Anax-MVC url-handler class
 * @Property  Object  $theme      Anax-MVC theme-handler class
 * @Property  Object  $views      Anax-MVC views-handler class
 * @Property  Object  $textFilter Anax-MVC textformat-handler class
 * @Property  Object  $db         PDO database class
 */
class ContentController implements \Anax\DI\IInjectionAware
{
  use \Anax\DI\TInjectable;
  
  /**
	 * Properties
	 */
  private $content = null;
  private $limitListPerPage = null;       // Limit contents on list page
  private $minimumLength    = 3;          // Minimum length on text-fields (ex. ingress, title etc)
  private $types 		        = [		        // Content types
		'blog-post'   => ['url' => 'blog/read/',  'field' => 'slug', 	'perfix' => '', 	'title' => 'Blog'],
		'page'        => ['url' => 'page/page/', 	'field' => 'url', 	'perfix' => '', 	'title' => 'Page']
	];
  private $filters = array('bbcode','clickable','markdown', 'nl2br', 'shortcode');
  private $urlPrefix = "content.php/";
  
  /**
   * Initialize the controller
   *
   * @Return    Void
   */
  public function initialize(){
    $this->content = new \Chp\TextContent\Content();
    $this->content->setDI($this->di);
  }
  
  /**
	 *  Index content - use listAction 
   *
	 * @Returns		Void
	 */
	public function indexAction(){
    $this->listAction();
  }
  
  /**
   * Setup content (If it allready exist it will restore database to begining)
   *
   * @Return    Void
   */
  public function setupAction(){
    $toDo   = "Restore or setup";
    $toWhat = "content database tables";
    $title  = "{$toDo} {$toWhat}";
    $form   = $this->confirmForm($this->url->create($this->urlPrefix . 'content/'));
    $status = $form->check();
 
    if($status === true){
    
      $this->db->dropTableIfExists('content')->execute();

      $this->db->createTable(
        'content',
        [
          'id'        => ['integer', 'primary key', 'not null', 'auto_increment'],
          'slug'      => ['char(80)'],
          'url'       => ['char(80)'],
          'type'      => ['char(80)'],
          'title'     => ['varchar(80)'],
          'ingress'   => ['text'],
          'text'      => ['text'],
          'filters'   => ['char(80)'],
          'author'    => ['integer', 'not null'],
          'published' => ['datetime'],
          'created'   => ['datetime'],
          'updated'   => ['datetime'],
          'deleted'   => ['datetime']
        ]
      )->execute();
      
      $this->db->insert(
        'content',
        ['slug', 'url', 'type', 'title', 'ingress', 'text', 'filters', 'author', 'published', 'created']
      );

      $now = date('Y-m-d H:i:s');

      $this->db->execute([
        'welcome_to_your_new_blog',
        NULL,
        'blog-post',
        'Welcome to your new blog',
        'This is example-blogg to show your new blog system!',
        'You can start blog by visit [url=' . $this->url->create($this->urlPrefix . "content/add/") . ']Add content[/url]',
        'bbcode',
        3,
        $now,
        $now
      ]);
      
      $this->db->execute([
        'welcome_to_your_new_page',
        'example-page',
        'page',
        'Welcome to your new page',
        'This is example-page to show your new page system!',
        'You can start makeing pages by visit [url=' . $this->url->create($this->urlPrefix . "content/add/") . ']Add content[/url]',
        'bbcode',
        3,
        $now,
        $now
      ]);
      
      $this->db->dropTableIfExists('content_tags')->execute();
      
      $this->db->createTable(
        'content_tags',
        [
          'idContent' => ['integer', 'not null'],
          'tag'       => ['varchar(150)', 'not null'],
          'slug'      => ['varchar(150)', 'not null']
        ]
      )->execute();
      
      $this->db->insert(
        'content_tags',
        ['idContent', 'tag', 'slug']
      );
      
      $this->db->execute([
        1,
        'New blog',
        'new_blog'
      ]);

      $this->db->execute([
        1,
        'Blog information',
        'blog_information'
      ]);
      
      $this->views->add('text-content/action-finish', [
          'title'   => $title,
          'msg'     => "Content and content tags database tables " . strtolower($toDo). " was successful!"
      ], 'main');
      
    }
    else{      
      $this->views->add('text-content/action', [
          'title'   => $title,
          'toDo'    => strtolower($toDo),
          'toWhat'  => $toWhat,
          'form'    => $form->getHTML(['novalidate' => true])
      ], 'main');
    }
    
    $this->theme->setTitle($title);
  }
  
  /**
	 *  List content (by type)
   *
	 * @Param     String    $type	  Type to list
   * @Param     Integer   $page   Page that paging is on
	 * @Return		Void
	 */
	public function listAction($type = null, $published = false, $page = null){
    $type      = ($this->checkType($type)) ? $type : null;
    $title     = "All Content {$type} listed";
    $published = boolval($published);

    $form   = $this->listForm($type, $published);
    $form->check();
    
    if(!is_null($type))
      $contents = $this->content->getAllContentOfType($type, $page, $this->limitListPerPage, $published);
    else
      $contents = $this->content->getAllContent($page, $this->limitListPerPage, $published);
    $contents = $this->prepareListContent($contents);
    
    $this->theme->setTitle($title);
    $this->views->add('text-content/list', 
      [
        'title'     => $title,
        'form'      => $form->getHTML(array('novalidate' => true)),
        'contents' 	=> $contents,
        'addUrl'	  => $this->url->create($this->urlPrefix . 'content/add/'),
        'setupUrl'  => $this->url->create($this->urlPrefix . 'content/setup/')
      ]
    );

  }
  
  /**
   * Add content to database
   *
   * @Return    Void
   */
  public function addAction(){
    $action = "Add";
    $title = "{$action} content";
    
    $form = $this->contentForm();
    $form->check();
    
    $this->theme->setTitle($title);
    $this->views->add('text-content/post', 
      [
        'title'  => $title,
        'action' => $action,
        'form'   => $form->getHTML(array('novalidate' => true))
      ]);
  }
  
  /**
   * Edit content in database
   *
   * @Param     Integer   $id   Index for content to edit
   * @Return    Void
   */
  public function editAction($id = null){
    $action = "Edit";
    $title  = "{$action} content";
    $url    = $this->url->create($this->urlPrefix . 'content/');
    
    if(is_null($id) || !is_numeric($id))
      $this->response->redirect($url);
    
    $content = $this->content->getContentById($id, false);
    
    if(is_null($content->id))
      $this->response->redirect($url);
    
    $form = $this->contentForm($content);
    $form->check();
    
    $this->theme->setTitle($title);
    
    $this->views->add('text-content/post', 
      [
        'title'  => $title,
        'action' => $action,
        'form'   => $form->getHTML(array('novalidate' => true))
      ]
    );
  }
	
	/**
	 * Remove content
	 *
   * @Param   Integer  $id      Index to content to remove
	 * @Return  String   $error  	Database-error msg
	 */
	public function removeAction($id = null){
		$action = "Delete";
    $title = "Delete content";
    $url = $this->url->create($this->urlPrefix . "content/");
    
    if(is_null($id) || !is_numeric($id)){
			$this->response->redirect($url);
		}
    
    $content = $this->content->find($id);
    
    if(is_null($content->id)){
      $this->response->redirect($url);
    }
    
    $form = $this->confirmForm($url);
    $status = $form->Check();
    
    if ($status === true) {
      $now = date('Y-m-d H:i:s');
      
      $content->deleted = $now;
      $content->save();
      
      $this->response->redirect($url);
    }
    
    $this->theme->setTitle($title);
    $this->views->add('text-content/action', [
      'title'  => $title,
      'toDo'    => strtolower($action),
      'toWhat'  => strtolower($this->getTypeTitle($content->type)),
      'which'   => htmlentities($content->title),
      'form'    => $form->getHTML(['novalidate' => true]),
    ], 'main');
	}
  
  /**
   * Prepare form to add or edit content
   *
   * @Param   String    $type       Selected content-type
   * @Param   Boolean   $published  If content should be published already
   * @Return  Object    $form       CForm object
   */
  private function listForm($type = null, $published = false){
    
    $type_options      = array_merge([0 => "Select a type of content"], $this->getTypes());
    
    $form = new \Mos\HTMLForm\CForm([], [
        'type' => [
          'type'        => 'select',
          'label'       => 'Type of content:',
          'options'     => $type_options,
          'value'       => (isset($type)) ? $type : ''
        ],
        'published' => [
          'type'        => 'checkbox',
          'label'       => 'Published',
          'checked'     => $published,
          'value'       => 1
        ],
        'submit' => [
          'value' 		=> 'Filter',
          'type'      => 'submit',
          'callback'  => function ($form) {
            $published = ($this->request->getPost('published')) ? 1 : 0;
            $type      = $form->Value('type');
            
            $this->response->redirect($this->url->create($this->urlPrefix . "content/list/{$type}/{$published}"));
          }
        ]
    ]);
    
    return $form;
  }
  
  /**
   * Prepare form to add or edit content
   *
   * @Param   Object    $values     Content values to add form elements
   * @Return  Object    $form       CForm object
   */
  private function contentForm($values = null){
    if(isset($values) && is_object($values)){
      $valArr = get_object_vars($values);
      extract($valArr);
    }
    
    $slug = (isset($slug)) ? $slug : NULL;
    
    $type_options      = $this->getTypes();
    
    $form = new \Mos\HTMLForm\CForm([], [
        'id'      => [
          'type'        => 'hidden',
          'value'       => (isset($id)) ? $id : 0
        ],
        'title' => [
          'type'        => 'text',
          'label'       => 'Title: (Between ' . $this->minimumLength . ' to 80 chars)',
          'maxlength'   => 80,
          'required'    => true,
          'value'       => (isset($title)) ? $title : '',
          'validation'  => [
                              'custom_test' => array(
                                'message' => 'Minmum length is ' . $this->minimumLength . ' chars.', 
                                'test'    => array($this, 'minimumLength')
                              )
                           ]
        ],
        'url' => [
          'type'        => 'text',
          'label'       => 'Url:',
          'maxlength'   => 80,
          'value'       => (isset($url)) ? $url : '',
          'required'    => false, 
          'validation'  => [
                              'custom_test' => array(
                                'message' => 'Url is not in accepted-format for url:s (accepted characters is \'a-z0-9-_()\').', 
                                'test'    => array($this, 'validateSlug')
                              )
                           ]
        ],
        'ingress' => [
          'type'        => 'textarea',
          'label'       => 'Ingress: (minimum 3 chars)',
          'value'       => (isset($ingress)) ? $ingress : '',
          'required'    => true,
          'validation'  => [
                              'custom_test' => array(
                                'message' => 'Minmum length is ' . $this->minimumLength . ' chars.', 
                                'test'    => array($this, 'minimumLength')
                              )
                           ]
        ],
        'text' => [
          'type'        => 'textarea',
        	'label'       => 'Text: (minimum 3 chars)',
          'value'       => (isset($text)) ? $text : '',
          'required'    => true,
          'validation'  => [
                            'custom_test' => array(
                              'message' => 'Minmum length is ' . $this->minimumLength . ' chars.', 
                              'test'    => array($this, 'minimumLength')
                            )
                           ]
        ],
        'type' => [
          'type'        => 'select',
          'label'       => 'Type of content:',
          'options'     => $type_options,
          'value'       => (isset($type)) ? $type : '',
          'required'    => true,
          'validation'  => [
                              'not_empty',
                              'custom_test' => array(
                                'message' => 'You need to select a existing type.', 
                                'test'    => array($this, 'checkType')
                              )
                           ]
        ],
        'tags' => [
          'type'        => 'text',
          'label'       => 'Tags: (Seperate by \',\')',
          'value'       => (isset($tags)) ? $tags : '',
          'required'    => false
        ],
        'filters' => [
          'type'        => 'checkbox-multiple',
          'label'       => 'Text filter:',
          'values'      => $this->filters,
          'checked'     => (isset($filters)) ? explode(',', $filters) : array(),
          'validation'  => [
                              'custom_test' => array(
                                'message' => 'You need to select a existing type.', 
                                'test'    => array($this, 'checkFilter')
                              )
                           ]
        ],
        'published' => [
          'type'        => 'datetime',
          'label'       => 'Published: (YYYY-MM-DD HH:MM:SS)',
          'value'       => (isset($published)) ? $published : '',
          'validation'  => [
                              'custom_test' => array(
                                'message' => 'It need to be in a correct date and time with format: YYYY-MM-DD HH:MM:SS.', 
                                'test'    => array($this, 'checkDatetime')
                              )
                           ]
        ],
        'publishedNow' => [
          'type'        => 'checkbox',
          'label'       => 'Publish now:',
          'checked'     => (!isset($published) && !isset($id)),
          'value'       => 'yes'
        ],
        'submit' => [
          'value' 		=> 'Save',
          'type'      => 'submit',
          'callback'  => function ($form) use($slug) {
            return $this->saveContent($form, $slug);
          }
        ]
    ]);
    
    return $form;
  }
  
  /**
   * Prepare confirmation form
   *
   * @Param   String   $returnUrl       Return url
   * @Return  Object   $form            Form-object
   */
  public function confirmForm($returnUrl = null){
    $returnUrl = (isset($returnUrl)) ? $returnUrl : $this->request->getBaseUrl();
    
    $form = new \Mos\HTMLForm\CForm([], [
        'submit' => [
          'type'     => 'submit',
          'value'    => 'Yes',
          'callback' => function() {
            return true;
          }
        ],
        'submit-no' => [
          'type'     => "submit",
          'value'    => 'No',
          'callback' => function() use($returnUrl) {
            $this->response->redirect($returnUrl);
          }
        ]
      ]
    );
		
    // Check the status of the form
    return $form;
  }
  
  /**
   * Save content to database
   *
   * @Param   Object    $form     Form object
   * @Param   String    $oldSlug  Old slug for content to compare
   * @Return  Boolean   false     If saving fail, return false
   */
  private function saveContent($form, $oldSlug = null){
    // Prepare content for saving
    $content = $this->prepareSaveContent($form, $oldSlug);
    
    // Save content
    $content = $this->content->save($content);
    
    // Saving fail
    if(!$this->content->id)
      return false;
    // Save tags for content to database
    else if($content['id'] != 0)
      $this->saveTags($form->Value('tags'), $this->content->id);
    
    $this->response->redirect($this->url->create($this->urlPrefix . "content/"));
  }

  /**
   * Save tags for content to database
   *
   * @Param   String   $tags     Tags for content
   * @Param   Int      $id       Content index
   */
  private function saveTags($tags, $id){
    $this->db->delete(
      'content_tags',
      'idContent = ?'
    )->execute([$id]);
    
    $this->db->insert(
      'content_tags',
      ['idContent', 'tag', 'slug']
    );
    
    if(isset($tags) && !is_null($tags)){
      $tagsArr = explode(",", $tags);
      
      foreach($tagsArr as $tag){
        $tag = trim($tag);
        $this->db->execute([
          $id,
          $tag,
          $this->slugify($tag)
        ]);
      }
    }
  }
  
  /**
   * Prepare contents for show in list view
   *
   * @Param   Object  $contents   Object with content objects
   * @Return  Array   $results    Array with prepare content objects
   */
  public function prepareListContent($contents){
    $results = array();
    
    foreach($contents AS $key => $content){
      $available = $this->checkIfAvailable($content->published);
      $results[$key] = (object)[];
      
      foreach($content as $key2 => $value){
        $results[$key]->{$key2} = $value;
      }
      
      $results[$key]->typeTxt      = $this->getTypeTitle($content->type);
      $results[$key]->title        = htmlspecialchars($content->title, ENT_QUOTES);
      $results[$key]->editUrl      = $this->url->create($this->urlPrefix . "content/edit/{$content->id}");
      $results[$key]->removeUrl    = $this->url->create($this->urlPrefix . "content/remove/{$content->id}");
      $results[$key]->showUrl      = $this->getUrlToContent($content);
      $results[$key]->available    = ((!$available) ? "not-" : null) . "published";
      $results[$key]->publishedTxt  = ($available) ? $contents[$key]->published : "Not published yet";
    }
    
    return $results;
  }
  /**
   * Prepare save of content to database
   *
   * @Param   Object    $form     Form object
   * @Param   String    $oldSlug  Old slug for content to compare
   * @Return  Array     $content  Prepare content array
   */  
  public function prepareSaveContent($form, $oldSlug = null){
    $now = date('Y-m-d H:i:s');
    
    // Prepare new slug
    $newSlug = $this->prepareNewSlug($form->Value('title'), $form->Value('type'), $oldSlug);
    
    $content = array(
      'title'     => $form->Value('title'),
      'slug'      => $newSlug,
      'url'       => $this->slugify($form->Value('url')),
      'ingress'   => $form->Value('ingress'),
      'text'      => $form->Value('text'),
      'type'      => $form->Value('type'),
      'filters'   => ($form->Value('filters')) ? implode(",", $form->Value('filters')) : '',
      'published' => ($form->Value('publishedNow') && $form->Value('publishedNow') == 'yes') ? $now : $form->Value('published')
    );
    
    $id = ($form->Value('id')) ? intval($form->Value('id')) : 0;
    
    if($id != 0){
      $content['updated'] = $now;
      $content['id']      = $id;
    }
    else{
      $content['created'] = $now;
      $content['author']  = 0;//$this->user->getUserId();
    }
    
    return $content;
  }
  
  /**
   * Prepare new slug for content by title
   *
   * @Param   String    $title      Content title to make slug by
   * @Param   String    $type       Content type
   * @Param   String    $oldSlug    Old slug for content to compare
   * @Return  String    $newSlug    New unique slug for content
   */
  public function prepareNewSlug($title, $type, $oldSlug = null){
    $newSlug = $this->slugify($title);
    
    if($oldSlug != $newSlug && isset($newSlug))
      $newSlug = $this->content->makeSlugToContent($newSlug, $type);
    
    return $newSlug;
  }
  
  /**
   * Check if content is published
   *
   * @Param   String      $datetime     When it will be published
   * @Return  Boolean     True/false    Validate result
   */
  public function checkIfAvailable($datetime){
    return ($datetime <= date('Y-m-d H:i:s')) ? true : false;
  }
  
  /**
	 * Create a link to the content, based on its type.
	 *
	 * @Param  	Object  	$content	Content to link to
	 * @Return 	String    	   	 	  With url for content
	 */
	public function getUrlToContent($content) {
    if(isset($this->types[$content->type])){
      $type = $this->types[$content->type]; // Get type from type index
	  
      return $this->url->create($this->urlPrefix . "{$type['url']}{$type['perfix']}{$content->{$type['field']}}");
    }
    
    return null;
	}
	
  /**
	 * Return array with all content types title and keys (Use for content-type select) 
	 *
	 * @Return		Array		$types	Array with the types title and keys
	 */
	public function getTypes(){
    $types = array();
    
		// Loop through and save types key as key and title as value in a new array
		foreach($this->types AS $key => $value){
			$types[$key] = $value['title'];
		}
		
		return $types;
	}
  
	/**
	 * Return name of one specific type
	 *
	 * @Params  String $type  Type key
	 * @Return  String        Type title
	 */
	public function getTypeTitle($type){
		return $this->types[$type]['title'];
	}
  
  /**
	 * Create a slug of a string, to be used as url.
	 *
	 * @Param   String   $str  String to format as slug.
	 * @Return  String   $str  Formatted slug. 
	 */
	public function slugify($str) {
	  $str = mb_strtolower(trim($str));
		
		$str = str_replace(array("å","ä","ö"), array("a","a","o"), utf8_decode(utf8_encode($str)));
		
	  $str = preg_replace('/[^a-z0-9-_()]/', '_', $str);
	  $str = trim(preg_replace('/_+/', '_', $str), '_');
	  return $str;
	}
  
	/**
	 * Check so the choosed type exist.
	 *
	 * @Param   	String		$type		Choosed type on content
	 * @Returns 	Boolean      			Validate result
	 */
	public function checkType($type){
		return isset($this->types[$type]); 
	}
  
  /**
   * Validate posted datetime so it is correct
   *
   * @Param   String    $datetime      Posted datetime to check  
   * @Return  Boolean   True/false     Validate status
   */
  public function checkDatetime($datetime){
    if(isset($datetime) && !empty($datetime)){
      $format = 'Y-m-d H:i:s';
      $d = \DateTime::createFromFormat($format, $datetime);
      return $d && $d->format($format) == $datetime;
    }
    return true;
  }
  
  /**
   * Minimum length (set by $this->minimumLength)
   *
   * @Param   String    $value        Value from form-element to validate
   * @Return  Boolean   True/false    Validate result
   */
  public function minimumLength($value){
    return (strlen($value) >= $this->minimumLength);
  }
  
  /**
   * Validate slug url
   *
   * @Param   String    $url          Url to validate
   * @Return  Boolean   True/false    True if valid otherwish false
   */
  public function validateSlug($url){
    return ($this->slugify($url) == $url);
  }
  
  /**
	 * Check so the select filters exist.
	 *
	 * @Param     Array 	  $filters  Array with select filters
	 * @Return    Boolean   $result   Return the result of test
	 */
	public function checkFilter($filter = null){
	  if(!empty($filter)){
      // For each filter, check if the filter exist
      foreach($this->filters as $val){
        if($val == $filter)
          return true;
      }
      return false;
    }
	  return true;
	}
}