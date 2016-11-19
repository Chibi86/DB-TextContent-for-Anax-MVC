<?php
namespace Chp\TextContent;

/**
 * A page controller
 * Made by Rasmus Berg (c) 2014-2016
 */
class PageController implements \Anax\DI\IInjectionAware
{
  use \Anax\DI\TInjectable;
  
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
    if(!$url){
      throw new \Anax\Exception\NotFoundException();
    }
    
    $page = $this->content->getContentByUrl($url, 'page');
    
    if(!$page){
      throw new \Anax\Exception\NotFoundException();
    }
    
    $page->title         = htmlentities($page->title, null, 'UTF-8');
    $page->ingress       = htmlentities($page->ingress, null, 'UTF-8');
    $page->text          = $this->textFilter->doFilter(htmlentities($page->text, null, 'UTF-8'), $page->filters);
    $page->editUrl       = $this->url->create($this->urlPrefix . "content/edit/{$page->id}"); 
    //$page->authorName    = htmlentities($page->name, null, 'UTF-8');
    //$page->authorUrl     = $this->url->create($this->urlPrefix . 'users/id/' . $page->author);
    
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
}
?>