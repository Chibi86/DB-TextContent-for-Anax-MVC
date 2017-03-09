<?php
namespace Anax\TextContent;
/**
 * A test class
 *
 */
class ContentControllerTest extends \PHPUnit_Framework_TestCase
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
		$this->app->ContentController = new \Anax\TextContent\ContentController();
		$this->app->ContentController->setDI($this->di);
	}
  
  /**
   * Test-case check if content is published
   *
   */
  public function testCheckIfAvailable(){
    $this->assertNotTrue($this->app->ContentController->checkIfAvailable('9999-12-31 23:59:59'), "Return true on not published content.");
    $this->assertTrue($this->app->ContentController->checkIfAvailable('1986-08-08 00:00:00'), "Return false on published content.");
  }
  
  /**
	 * Test-case create a link to the content, based on it's type.
	 *
	 */
	public function testGetUrlToContent() {
    $app = $this->app;
    
    $this->assertNull($app->ContentController->getUrlToContent((object) ['type' => 'test']), "Return url on no existing content-type.");
    $this->assertNotNull($app->ContentController->getUrlToContent((object) ['type' => 'blog-post', 'slug' => 'test']), "Returns no url on content-type 'blog-post'");
	}
  
  /**
	 * Test-case get content types array 
	 *
	 */
	public function testGetTypes(){
		$app = $this->app;
    
    $types = $app->ContentController->getTypes();
		
    $this->assertTrue((count($types) > 0), "Returns no content-types.");
	}
  
  /**
	 * Test-case check so the choosed type exist.
	 *
	 */
	public function testCheckType(){
    $app = $this->app;
		
    $this->assertNotTrue($app->ContentController->checkType('test'), "Return true on no existing content-type.");
    $this->assertTrue($app->ContentController->checkType('page'), "Return false on content-type 'page'.");
	}
  
  /**
   * Test-case validate filters so they exist
   *
   */
  public function testCheckFilter(){
    $app = $this->app;
		
    $this->assertNotTrue($app->ContentController->checkFilter('test'), "Return true on no existing filter.");
    $this->assertTrue($app->ContentController->checkFilter('nl2br'), "Don't find filter for Auto line breaks (nl2br).");
  }
  
  /**
   * Test-case minimum length
   *
   */
  public function testMinimumLength(){
    $app = $this->app;
		
    $this->assertNotTrue($app->ContentController->minimumLength(""), "Return true on empty string");
    $this->assertTrue($app->ContentController->minimumLength("Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                                                    Quisque aliquet ex nisi, sed pellentesque leo consectetur dictum. 
                                                    Mauris non urna turpis. Phasellus in risus consequat."),
                      "Return false on long text.");
  }
  
  /**
   * Test-case validate slug url
   *
   */
  public function testValidateSlug(){
    $app = $this->app;
		
    $this->assertNotTrue($app->ContentController->validateSlug('едц @&%#', "Return true on no slugify-text."));
    $this->assertTrue($app->ContentController->validateSlug('test_2000', "Return false on slugify-text."));
  }
}
?>