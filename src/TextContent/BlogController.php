<?php
namespace Chp\TextContent;

/**
 * A blog controller
 * Made by Rasmus Berg (c) 2014-2017
 *
 * @Property  Object  $this->di         Anax-MVC class handler
 * @Property  Object  $this->response   Anax-MVC Php Header class
 * @Property  Object  $this->url        Anax-MVC url-handler class
 * @Property  Object  $this->theme      Anax-MVC theme-handler class
 * @Property  Object  $this->views      Anax-MVC views-handler class
 * @Property  Object  $this->textFilter Anax-MVC textformat-handler class
 */
class BlogController implements \Anax\DI\IInjectionAware
{
  use \Anax\DI\TInjectable;
	
  /**
	 * Properties
	 */
  private $content = null;
  private $postsPerPage = null;
  private $urlPrefix = "content.php/";
  private $content = null;
  
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
   * Index action - uses tagAction whitout tag
   *
   * @Return   Void
   */
  public function indexAction(){
    $this->tagAction();
  }
  
  /**
   * Get blog posts by tag
   *
   * @Param   String    $tag    Blog-tag
   * @Param   Integer   $page   Page on paging
   * @Return  Void
   */
  public function tagAction($tag = null, $page = null){
    
    $tag_title = null;
    
    if(!is_null($tag))
      $posts = $this->content->getAllContentOfTag($tag, 'blog-post', $page, $this->postsPerPage);
    else
      $posts = $this->content->getAllContentOfType('blog-post', $page, $this->postsPerPage);
    
    if(count($posts) == 0 && !is_null($tag)){
      $this->response->redirect($this->url->create($this->urlPrefix . 'blog/'));
    }
    else if(count($posts) > 0){
      foreach($posts AS $key => $post){
        $posts[$key]->title         = htmlentities($post->title, null, 'UTF-8');
        $posts[$key]->ingress       = htmlentities($post->ingress, null, 'UTF-8');
        $posts[$key]->showUrl       = $this->url->create($this->urlPrefix . "blog/read/" . $post->slug);
        //$posts[$key]->authorName    = htmlentities($post->name, null, 'UTF-8');
        //$posts[$key]->authorUrl     = $this->url->create($this->urlPrefix . 'users/id/' . $post->author);
        
        $tags = $this->content->getTagsForContent($post->id);
        
        foreach($tags AS $item_key => $item){
          $tags[$item_key]->tag  = htmlentities($item->tag, null, 'UTF-8');
          $tags[$item_key]->url  = $this->url->create($this->urlPrefix . "blog/tag/{$item->slug}");
          
          if($tag == $item->slug && !isset($tag_title)){
            $tag_title = $item->tag;
          }
        }
        
        $posts[$key]->tags = $tags;
      }
    }
    
    $title = "Blog" . (($tag) ? " - tag: {$tag_title}" : NULL);
    
    $this->theme->setTitle($title);
    $this->views->add('text-content/blog-index', 
      [
        'title'     => $title,
        'posts' 	  => $posts,
        'tag'       => $tag_title
      ]
    );
  }
  
  /**
   * Get blog post by slug
   *
   * @Param   String    $slug    Blog-tag
   * @Return  Void
   */
  public function readAction($slug = null){
    
    if(is_null($slug)){
      $this->response->redirect($this->url->create($this->urlPrefix . 'blog/'));
    }
    
    $post = $this->content->getContentBySlug($slug, 'blog-post');
    
    if(empty($post)){
      $this->response->redirect($this->url->create($this->urlPrefix . 'blog/'));
    }
    
    $post->title         = htmlentities($post->title, null, 'UTF-8');
    $post->ingress       = htmlentities($post->ingress, null, 'UTF-8');
    $post->text          = $this->textFilter->doFilter(htmlentities($post->text, null, 'UTF-8'), $post->filters);
    $post->editUrl       = $this->url->create($this->urlPrefix . "content/edit/{$post->id}"); 
    $post->showUrl       = $this->url->create($this->urlPrefix . "blog/read/" . $post->slug);
    //$post->authorName    = htmlentities($post->name, null, 'UTF-8');
    //$post->authorUrl     = $this->url->create($this->urlPrefix . 'users/id/' . $post->author);
    
    $tags = $this->content->getTagsForContent($post->id);
    
    foreach($tags AS $tag_key => $tag){
      $tags[$tag_key]->tag = htmlentities($tag->tag, null, 'UTF-8');
      $tags[$tag_key]->url  = $this->url->create($this->urlPrefix . "blog/tag/{$tag->slug}");
    }
    
    $post->tags = $tags;
    
    $title = "Blog -  {$post->title}";
    
    $this->theme->setTitle($title);
    $this->views->add('text-content/blog-post', 
      [
        'title'         => $title,
        'post' 	        => $post,
        'blogIndexUrl'  => $this->url->create($this->urlPrefix . 'blog/')
      ]
    );
    
    /*$this->dispatcher->forward([
      'controller' => 'comment',
      'action'     => 'view',
      'params'	   =>	["blog/read/{$post->slug}"]
    ]);*/
  }
}