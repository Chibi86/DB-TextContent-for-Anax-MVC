<?php
namespace Chp\TextContent;
/**
 * A test class
 *
 */
class PageControllerTest extends \PHPUnit_Framework_TestCase
{
	private $app;
	private $di;
  
	/**
	 * Basic setup for database to perform tests against.
	 *
	 */
	protected function setUp()
	{
		$this->di = new \Anax\DI\CDIFactoryDefault();
		$this->app = new \Anax\MVC\CApplicationBasic($this->di);
    
    $this->initializeApp();
  }
  
  /**
	 * Helper function to initialize $di and $app
	 *
	 */
	private function initializeApp()
	{
		$this->app->PageController = new \Chp\TextContent\PageController();
		$this->app->PageController->setDI($this->di);
	}
  
  /**
   * Testcase prepare page
   *
   */
  public function testPreparePage(){
    try{
      $this->app->PageController->preparePage();
    }
    catch(Exception $e){
      $this->assertTrue(false, "Code fail when try to prepare null! Caught exception: {$e}.");
    }
    
    $page = (object)[
      'title'      => '<script>abc</script>',
      'ingress'    => '<script>abc</script>',
      'text'       => '<script>abc</script>',
      'filters'    => 'bbcode',
      'id'         => 1
    ];
    
    $result = $this->app->PageController->preparePage($page);
    
    $this->assertNotEquals($result->title, $page->title, "Page title should be htmlentities, but the result is the same.");
    $this->assertNotEquals($result->ingress, $page->ingress, "Page ingress should be htmlentities, but the result is the same.");
    $this->assertNotEquals($result->text, $page->text, "Page text should be htmlentities, but the result is the same.");
    //$this->assertNotEquals($result->authorName, $page->name, "Page (author) name should be htmlentities, but the result is the same.");
    $this->assertTrue(isset($result->editUrl), "Page edit url should exist!");
    //$this->assertTrue(isset($result->authorUrl), "Page author url should exist!");
    
    $page->text = "[b]abc[/b]";
    
    $result = $this->app->PageController->preparePage($page);
    
    $this->assertNotEquals($result->text, $page->text, "Page text should be filter with bbcode, but the result is the same.");
  }
}