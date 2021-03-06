<?php
namespace Chp\TextContent;

include_once(__DIR__ . '/../../app/config/text-content.php');

/**
 * A blog controller
 * Made by Rasmus Berg (c) 2014-2017
 *
 * @property  object  $di         Anax-MVC class handler
 * @property  object  $response   Anax-MVC Php Header class
 * @property  object  $url        Anax-MVC url-handler class
 * @property  object  $theme      Anax-MVC theme-handler class
 * @property  object  $views      Anax-MVC views-handler class
 * @property  object  $textFilter Anax-MVC textformat-handler class
 */
class BlogController implements \Anax\DI\IInjectionAware
{
  use \Anax\DI\TInjectable;
	
  /**
   * Properties
   */
  private $content = null;
  private $postsPerPage;
  private $urlPrefix;
  
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
    
    $this->postsPerPage = CHP_TC_POSTSPERPAGE;
    $this->urlPrefix = CHP_TC_URLPREFIX;
  }
  
  /**
   * Index action - uses tagAction whitout tag
   *
   * @return   void
   */
  public function indexAction($page = null){
    $posts = $this->getBlogContent($page);
    
    if(count($posts) > 0){
      foreach($posts AS $key => $post){
        // Prepare blog post for show in view
        $posts[$key] = $this->preparePost($post);
      }
    }
    
    // Show blog posts in view
    $this->postsToView($posts, null);
  }
  
  /**
   * Get blog posts by tag
   *
   * @param   string  $tag    Blog-tag
   * @param   int     $page   Page on paging
   * @return  void
   */
  public function tagAction($tag = null, $page = null){    
    $posts = $this->getBlogContent($page, $tag);
    
    if(count($posts) == 0 && !is_null($tag))
      $this->response->redirect($this->url->create("{$this->urlPrefix}blog/"));

    foreach($posts AS $key => $post){
      // Prepare blog post for show in view
      $posts[$key] = $this->preparePost($post);
    }
    
    $this->postsToView($posts, $tag);
  }
  
  /**
   * Get blog post by slug
   *
   * @param   string    $slug    Blog-tag
   * @return  void
   */
  public function readAction($slug = null){
    if(is_null($slug)){
      $this->response->redirect($this->url->create("{$this->urlPrefix}blog/"));
    }
    
    $post = $this->content->getContentBySlug($slug, 'blog-post');
    
    if(empty($post)){
      $this->response->redirect($this->url->create("{$this->urlPrefix}blog/"));
    }
    
    // Prepare blog post for show in view
    $post = $this->preparePost($post);
    
    $title = "Blog -  {$post->title}";
    
    $this->theme->setTitle($title);
    $this->views->add('text-content/blog-post', 
      [
        'title'         => $title,
        'post' 	        => $post,
        'blogIndexUrl'  => $this->url->create("{$this->urlPrefix}blog/")
      ]
    );
    
    /*$this->dispatcher->forward([
      'controller' => 'comment',
      'action'     => 'view',
      'params'	   =>	["{$this->urlPrefix}blog/read/{$post->slug}"]
    ]);*/
  }
  
  /**
   * Get blog posts by tag or type
   *
   * @param   int     $page    Page asked for
   * @param   string  $tag     Tag-slug to search for
   * @return  object  $posts   A object with post objects
   */
  public function getBlogContent($page = null, $tag = null){
    if(!is_null($tag))
      return $this->content->getAllContentOfTag($tag, 'blog-post', $page, $this->postsPerPage);
    else
      return $this->content->getAllContentOfType('blog-post', $page, $this->postsPerPage);
  }
  
  /**
   * Prepare blog post to show in view 
   *
   * @param   object  $post   Blog post object
   * @return  object  $result Prepared blog post object
   */
  public function preparePost($post){    
    $result = null;
    
    if(is_object($post) && property_exists($post, 'title')){
      $result = (object)[];
      
      foreach($post as $key => $value){
        $result->{$key} = $value;
      }
      
      $result->title        = htmlspecialchars($post->title, ENT_QUOTES);
      $result->ingress      = htmlspecialchars($post->ingress, ENT_QUOTES);
      $result->text         = $this->textFilter->doFilter(htmlspecialchars($post->text, ENT_QUOTES), $post->filters);
      $result->editUrl      = $this->url->create("{$this->urlPrefix}content/edit/{$post->id}");
      $result->showUrl      = $this->url->create("{$this->urlPrefix}blog/read/" . $post->slug);
      //$result->authorId     = $post->author;
      //$result->authorName   = htmlspecialchars($post->name, ENT_QUOTES);
      //$result->authorUrl    = $this->url->create("{$this->urlPrefix}users/id/{$post->author}");
      
      //unset($result->author);
      
      $tags = $this->content->getTagsForContent($post->id);
      
      foreach($tags AS $item_key => $item){
        $tags[$item_key]->tag  = htmlspecialchars($item->tag, ENT_QUOTES);
        $tags[$item_key]->url  = $this->url->create($this->urlPrefix . "blog/tag/{$item->slug}");
      }
      
      $result->tags = $tags;
    }

    return $result;
  }
  
  /**
   * Show blog posts in view
   *
   * @param   object  $posts   Object of blog post objects
   * @param   string  $tag     Tag-slug which has give this result
   * @return  void
   */
  private function postsToView($posts, $tag = null){
    $tag_title = null;
    
    if(!is_null($tag))
      $tag_title = $this->content->getTagBySlug($tag);
    
    $title = "Blog" . ((!is_null($tag_title)) ? " - tag: {$tag_title}" : NULL);
  
    $this->theme->setTitle($title);
    $this->views->add('text-content/blog-index', 
      [
        'title'     => $title,
        'posts' 	  => $posts,
        'tag'       => $tag_title
      ]
    );
  }
}