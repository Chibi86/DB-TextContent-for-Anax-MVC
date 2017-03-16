<?php
namespace Chp\TextContent;

include_once(__DIR__ . '/../../app/config/text-content.php');

/**
 * Text content controller
 * Made by Rasmus Berg (c) 2014-2017
 *
 * @property  object  $di         Anax-MVC class handler
 * @property  object  $request    Anax-MVC $_POST, $_GET and $_SERVER handler class
 * @property  object  $response   Anax-MVC Php Header class
 * @property  object  $url        Anax-MVC url-handler class
 * @property  object  $theme      Anax-MVC theme-handler class
 * @property  object  $views      Anax-MVC views-handler class
 * @property  object  $textFilter Anax-MVC textformat-handler class
 * @property  object  $db         PDO database class
 */
class ContentController implements \Anax\DI\IInjectionAware
{
  use \Anax\DI\TInjectable;
  
  /**
	 * Properties
	 */
  private $content;
  private $valid;
  private $contentPerPage; // Limit contents on list page
  private $urlPrefix;
  private $miniLength;
  
  /**
   * Initialize the controller
   *
   * @return    void
   */
  public function initialize(){
    if(!is_object($this->di))
      throw new \Anax\Exception\InternalServerErrorException('"$this->di" is not valid!');
    
    $this->content = new \Chp\TextContent\Content();
    $this->content->setDI($this->di);
    $this->valid = new \Chp\TextContent\ValidContent();
    $this->valid->setDI($this->di);
    $this->valid->initialize();
    
    $this->contentPerPage = CHP_TC_CONTENTPERPAGE;
    $this->urlPrefix      = CHP_TC_URLPREFIX;
    $this->miniLength     = CHP_TC_MINILENGTH;
  }
  
  /**
	 * Index content - use listAction 
   *
	 * @return		void
	 */
	public function indexAction(){
    $this->listAction();
  }
  
  /**
   * Setup content (If it allready exist it will restore database to begining)
   *
   * @return    void
   */
  public function setupAction(){
    $toDo   = "Restore or setup";
    $toWhat = "content database tables";
    $title  = "{$toDo} {$toWhat}";
    $form   = $this->confirmForm($this->url->create("{$this->urlPrefix}content/"));
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
        'You can start blog by visit [url=' . $this->url->create("{$this->urlPrefix}content/add/") . ']Add content[/url]',
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
        'You can start makeing pages by visit [url=' . $this->url->create("{$this->urlPrefix}content/add/") . ']Add content[/url]',
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
	 * @param     string  $type	  Type to list
   * @param     int     $page   Page that paging is on
	 * @return		void
	 */
	public function listAction($type = null, $published = false, $page = null){
    $type      = ($this->valid->checkType($type)) ? $type : null;
    $title     = "All Content {$type} listed";
    $published = boolval($published);

    $form   = $this->listForm($type, $published);
    $form->check();
    
    if(!is_null($type))
      $contents = $this->content->getAllContentOfType($type, $page, $this->contentPerPage, $published);
    else
      $contents = $this->content->getAllContent($page, $this->contentPerPage, $published);
    
    $contents = $this->prepareListContent($contents);
    
    $this->theme->setTitle($title);
    $this->views->add('text-content/list', 
      [
        'title'     => $title,
        'form'      => $form->getHTML(array('novalidate' => true)),
        'contents' 	=> $contents,
        'addUrl'	  => $this->url->create("{$this->urlPrefix}content/add/"),
        'setupUrl'  => $this->url->create("{$this->urlPrefix}content/setup/")
      ]
    );

  }
  
  /**
   * Add content to database
   *
   * @return    void
   */
  public function addAction(){
    $action = "Add";
    $title = "{$action} content";
    
    $form = $this->contentForm();
    $form->check();
    
    $this->theme->setTitle($title);
    
    $this->views->add('text-content/post', [
        'title'  => $title,
        'action' => $action,
        'form'   => $form->getHTML(array('novalidate' => true))
    ]);
  }
  
  /**
   * Edit content in database
   *
   * @param     int   $id   Index for content to edit
   * @return    void
   */
  public function editAction($id = null){
    $action = "Edit";
    $title  = "{$action} content";
    $url    = $this->url->create("{$this->urlPrefix}content/");
    
    if(is_null($id) || !is_numeric($id))
      $this->response->redirect($url);
    
    $content = $this->content->getContentById($id, false);
    
    if(is_null($content->id))
      $this->response->redirect($url);
    
    $form = $this->contentForm($content);
    $form->check();
    
    $this->theme->setTitle($title);
    
    $this->views->add('text-content/post', [
        'title'  => $title,
        'action' => $action,
        'form'   => $form->getHTML(array('novalidate' => true))
    ]);
  }
	
	/**
	 * Remove content
	 *
   * @param   int     $id       Index to content to remove
	 * @return  string  $error    Database-error msg
	 */
	public function removeAction($id = null){
		$action = "Delete";
    $title = "Delete content";
    $url = $this->url->create("{$this->urlPrefix}content/");
    
    if(is_null($id) || !is_numeric($id))
			$this->response->redirect($url);
    
    $content = $this->content->find($id);
    
    if(is_null($content->id))
      $this->response->redirect($url);
    
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
      'toWhat'  => strtolower($this->valid->getTypeTitle($content->type)),
      'which'   => htmlspecialchars($content->title, ENT_QUOTES),
      'form'    => $form->getHTML(['novalidate' => true]),
    ], 'main');
	}
  
  /**
   * Prepare form to add or edit content
   *
   * @param   string                $type       Selected content-type
   * @param   boolean               $published  If content should be published already
   * @return  \Mos\HTMLForm\CForm   $form       CForm object
   */
  private function listForm($type = null, $published = false){
    
    $type_options      = array_merge([0 => "Select a type of content"], $this->valid->getTypes());
    
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
            
            $this->response->redirect($this->url->create("{$this->urlPrefix}content/list/{$type}/{$published}"));
          }
        ]
    ]);
    
    return $form;
  }
  
  /**
   * Prepare form to add or edit content
   *
   * @param   object                $values     Content values to add form elements
   * @return  \Mos\HTMLForm\CForm   $form       CForm object
   */
  private function contentForm($values = null){
    if(isset($values) && is_object($values)){
      $valArr = get_object_vars($values);
      extract($valArr);
    }
    
    $slug = (isset($slug)) ? $slug : NULL;
    
    $type_options = $this->valid->getTypes();
    
    $form = new \Mos\HTMLForm\CForm([], [
        'id'      => [
          'type'        => 'hidden',
          'value'       => (isset($id)) ? $id : 0
        ],
        'title' => [
          'type'        => 'text',
          'label'       => 'Title: (Between ' . $this->miniLength . ' to 80 chars)',
          'maxlength'   => 80,
          'required'    => true,
          'value'       => (isset($title)) ? $title : '',
          'validation'  => [
                              'custom_test' => array(
                                'message' => 'Minmum length is ' . $this->miniLength . ' chars.', 
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
                                'message' => 'Minmum length is ' . $this->miniLength . ' chars.', 
                                'test'    => array($this, 'minimumLength')
                              )
                           ]
        ],
        'text' => [
          'type'        => 'textarea',
        	'label'       => 'Text: (minimum ' . $this->miniLength . ' chars)',
          'value'       => (isset($text)) ? $text : '',
          'required'    => true,
          'validation'  => [
                            'custom_test' => array(
                              'message' => 'Minmum length is ' . $this->miniLength . ' chars.', 
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
          'values'      => $this->valid->getFilters(),
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
   * @param   string                $returnUrl       Return url
   * @return  \Mos\HTMLForm\CForm   $form            Form-object
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
   * @param   \Mos\HTMLForm\CForm   $form       Form object
   * @param   string                $oldSlug    Old slug for content to compare
   * @return  boolean               false/true  If saving fail, return false
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
    
    $this->response->redirect($this->url->create("{$this->urlPrefix}content/"));
    return true;
  }

  /**
   * Save tags for content to database
   *
   * @param   string   $tags     Tags for content
   * @param   int      $id       Content index
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
          $this->valid->slugify($tag)
        ]);
      }
    }
  }
  
  /**
   * Prepare contents for show in list view
   *
   * @param   object   $contents    Object with content objects
   * @return  array                 $results    Array with prepare content objects
   */
  public function prepareListContent($contents){
    $results = array();
    
    foreach($contents AS $key => $content){
      $available = $this->valid->checkIfAvailable($content->published);
      $results[$key] = (object)[];
      
      foreach($content as $key2 => $value){
        $results[$key]->{$key2} = $value;
      }
      
      $results[$key]->typeTxt      = $this->valid->getTypeTitle($content->type);
      $results[$key]->title        = htmlspecialchars($content->title, ENT_QUOTES);
      $results[$key]->editUrl      = $this->url->create("{$this->urlPrefix}content/edit/{$content->id}");
      $results[$key]->removeUrl    = $this->url->create("{$this->urlPrefix}content/remove/{$content->id}");
      $results[$key]->showUrl      = $this->valid->getUrlToContent($content);
      $results[$key]->available    = ((!$available) ? "not-" : null) . "published";
      $results[$key]->publishedTxt  = ($available) ? $contents[$key]->published : "Not published yet";
    }
    
    return $results;
  }
  
  /**
   * Prepare save of content to database
   *
   * @param   \Mos\HTMLForm\CForm   $form     Form object
   * @param   string                $oldSlug  Old slug for content to compare
   * @return  array                 $content  Prepare content array
   */  
  public function prepareSaveContent($form, $oldSlug = null){
    $now = date('Y-m-d H:i:s');
    
    // Prepare new slug
    $newSlug = $this->prepareNewSlug($form->Value('title'), $form->Value('type'), $oldSlug);
    
    $content = array(
      'title'     => $form->Value('title'),
      'slug'      => $newSlug,
      'url'       => $form->Value('url'),
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
   * @param   string    $title      Content title to make slug by
   * @param   string    $type       Content type
   * @param   string    $oldSlug    Old slug for content to compare
   * @return  string    $newSlug    New unique slug for content
   */
  public function prepareNewSlug($title, $type, $oldSlug = null){
    $newSlug = $this->valid->slugify($title);
    
    if($oldSlug != $newSlug && isset($newSlug))
      $newSlug = $this->content->makeSlugToContent($newSlug, $type);
    
    return $newSlug;
  }
  
  /**
   * Validate minimum length
   *
   * @param   string    $text       Text to validate
   * @return  boolean   True/False  Validate status
   */
  public function minimumLength($text = null){
    return $this->valid->minimumLength($text);
  }
  
  /**
   * Validate slug
   *
   * @param   string    $slug       Slug to validate
   * @return  boolean   True/False  Validate status
   */
  public function validateSlug($slug = null){
    return $this->valid->validateSlug($slug);
  }
  
  /**
   * Validate type
   *
   * @param   string    $type   Type to validate
   * @return  boolean   True/False  Validate status
   */
  public function checkType($type = null){
    return $this->valid->checkType($type);
  }
  
  /**
   * Validate filter
   *
   * @param   string    $filter   Filter to validate
   * @return  boolean   True/False  Validate status
   */
  public function checkFilter($filter = null){
    return $this->valid->checkFilter($filter);
  }
  
  /**
   * Validate date time
   *
   * @param   string    $datetime   Date time to validate
   * @return  boolean   True/False  Validate status
   */
  public function checkDatetime($datetime = null){
    return $this->valid->checkDatetime($datetime);
  }
}