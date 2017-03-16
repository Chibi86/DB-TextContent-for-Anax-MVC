<?php
namespace Chp\TextContent;

include_once(__DIR__ . '/../../app/config/text-content.php');

/**
 * A page controller
 * Made by Rasmus Berg (c) 2014-2017
 *
 * @Property  Object  $di         Anax-MVC class handler
 * @Property  Object  $request    Anax-MVC $_POST, $_GET and $_SERVER handler class
 * @Property  Object  $url        Anax-MVC url-handler class
 * @Property  Object  $theme      Anax-MVC theme-handler class
 * @Property  Object  $views      Anax-MVC views-handler class
 * @Property  Object  $textFilter Anax-MVC textformat-handler class
 */
class PageController implements \Anax\DI\IInjectionAware
{
  use \Anax\DI\TInjectable;
  
  /**
	 * Properties
	 */
  private $content = null;
  private $urlPrefix = CHP_TC_URLPREFIX;
  
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
   * Index content - Redirect to startpage
   *
   * @Return    Void
   */
  public function indexAction(){
    $url = $this->request->getGet('url');
    $this->pageAction($url);
  }
  
  /**
   * Visit page made by database content 
   *
   * @Param   String    $url    Select url to content  
   * @Return  Void
   */
  public function pageAction($url = null){
    if(is_null($url))
      throw new \Anax\Exception\NotFoundException();
    
    $page = $this->content->getContentByUrl($url, 'page');
    
    if(empty($page))
      throw new \Anax\Exception\NotFoundException();
    
    // Prepare page for show in view
    $page = $this->preparePage($page);
    
    $title = $page->title;
    
    $this->theme->setTitle($title);
    $this->views->addString($title, 'page-title');
    $this->views->add('text-content/page', 
      [
        'title'         => $title,
        'page' 	        => $page
      ]
    );
  }
  
  /**
   * Prepare page to show in view 
   *
   * @Param   Object    $page    Page information  
   * @Return  Object    $result  Prepared page information
   */
  public function preparePage($page = null){
    $result = null;
    
    if(!is_null($page)){
      $result = (object)[];
      
      foreach($page as $key => $value){
        $result->{$key} = $value;
      }
      
      $result->title         = htmlspecialchars($page->title, ENT_QUOTES);
      $result->ingress       = htmlspecialchars($page->ingress, ENT_QUOTES);
      $result->text          = $this->textFilter->doFilter(htmlentities($page->text, ENT_QUOTES), $page->filters);
      $result->editUrl       = $this->url->create("content/edit/{$page->id}");
      //$result->authorId      = $page->author;
      //$result->authorName    = htmlentities($page->name, ENT_QUOTES);
      //$result->authorUrl     = $this->url->create('users/id/' . $page->author);
      
      //unset($result->author);
    }
    
    return $result;
  }
}