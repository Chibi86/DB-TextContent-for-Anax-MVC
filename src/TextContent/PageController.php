<?php
namespace Chp\TextContent;

include_once(__DIR__ . '/../../app/config/text-content.php');

/**
 * A page controller
 * Made by Rasmus Berg (c) 2014-2017
 *
 * @property  object  $di         Anax-MVC class handler
 * @property  object  $request    Anax-MVC $_POST, $_GET and $_SERVER handler class
 * @property  object  $url        Anax-MVC url-handler class
 * @property  object  $theme      Anax-MVC theme-handler class
 * @property  object  $views      Anax-MVC views-handler class
 * @property  object  $textFilter Anax-MVC textformat-handler class
 */
class PageController implements \Anax\DI\IInjectionAware
{
  use \Anax\DI\TInjectable;
  
  /**
   * Properties
   */
  private $content;
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
    
    $this->urlPrefix = CHP_TC_URLPREFIX;
  }
  
  /**
   * Index content - Redirect to startpage
   *
   * @return    void
   */
  public function indexAction(){
    $url = $this->request->getGet('url');
    $this->pageAction($url);
  }
  
  /**
   * Visit page made by database content 
   *
   * @param   string    $url    Select url to content  
   * @return  void
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
   * @param   object    $page    Page information  
   * @return  object    $result  Prepared page information
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
      $result->text          = $this->textFilter->doFilter(htmlspecialchars($page->text, ENT_QUOTES), $page->filters);
      $result->editUrl       = $this->url->create("{$this->urlPrefix}content/edit/{$page->id}");
      //$result->authorId      = $page->author;
      //$result->authorName    = htmlspecialchars($page->name, ENT_QUOTES);
      //$result->authorUrl     = $this->url->create("{$this->urlPrefix}users/id/{$page->author}");
      
      //unset($result->author);
    }
    
    return $result;
  }
}