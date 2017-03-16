<?php
namespace Chp\TextContent;

/**
 * A test class
 *
 */
class ValidContentTest extends \PHPUnit_Framework_TestCase
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
		$this->app->ValidContent = new \Chp\TextContent\ValidContent();
		$this->app->ValidContent->setDI($this->di);
    $this->app->ValidContent->initialize();
	}
  
  /**
	 * Test-case create a link to the content, based on it's type.
	 *
	 */
	public function testGetUrlToContent() {
    $app = $this->app;
    
    $this->assertNull($app->ValidContent->getUrlToContent((object) ['type' => 'test']), "Return url on no existing content-type.");
    $this->assertNotNull($app->ValidContent->getUrlToContent((object) ['type' => 'blog-post', 'slug' => 'test']), "Returns no url on content-type 'blog-post'");
	}
  
  /**
	 * Test-case get content types array 
	 *
	 */
	public function testGetTypes(){
		$app = $this->app;
    
    $types = $app->ValidContent->getTypes();
		
    $this->assertTrue((count($types) > 0), "Returns no content-types.");
	}
  
  /**
	 * Test-case get filters 
	 *
	 */
  public function testGetFilters(){
    $filters = $this->app->ValidContent->getFilters();
    
    $this->assertTrue((count($filters) > 0), "Returns no text filters.");
  }
  
  /**
   * Test-case check if content is published
   *
   */
  public function testCheckIfAvailable(){
    $this->assertNotTrue($this->app->ValidContent->checkIfAvailable('9999-12-31 23:59:59'), "Return true on not published content.");
    $this->assertTrue($this->app->ValidContent->checkIfAvailable('1986-08-08 00:00:00'), "Return false on published content.");
  }
  
  /**
	 * Test-case check so the choosed type exist.
	 *
	 */
	public function testCheckType(){
    $app = $this->app;
		
    $this->assertNotTrue($app->ValidContent->checkType('test'), "Return true on no existing content-type.");
    $this->assertTrue($app->ValidContent->checkType('page'), "Return false on content-type 'page'.");
	}
  
  /**
   * Test-case check date time
   *
   */
  public function testCheckDatetime(){
    $app = $this->app;
		
    $this->assertNotTrue($app->ValidContent->checkDatetime('abc'), "Return true on 'abc' is date/time."); 
    $this->assertTrue($app->ValidContent->checkDatetime('1986-08-08 08:08:08'), "Return false on '1986-08-08 08:08:08' is date/time.");
    $this->assertTrue($app->ValidContent->checkDatetime(''), "Return false on empty string, should validate true (Empty is acceptable).");
  }
  
  /**
   * Test-case validate filters so they exist
   *
   */
  public function testCheckFilter(){
    $app = $this->app;
		
    $this->assertTrue($app->ValidContent->checkFilter(), "Return false on no filter.");
    $this->assertNotTrue($app->ValidContent->checkFilter('test'), "Return true on no existing filter.");
    $this->assertTrue($app->ValidContent->checkFilter('nl2br'), "Don't find filter for Auto line breaks (nl2br).");
  }
  
  /**
   * Test-case minimum length
   *
   */
  public function testMinimumLength(){
    $app = $this->app;
		
    $this->assertNotTrue($app->ValidContent->minimumLength(""), "Return true on empty string");
    $this->assertTrue($app->ValidContent->minimumLength("Lorem ipsum dolor sit amet, consectetur adipiscing elit.
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
		
    $this->assertNotTrue($app->ValidContent->validateSlug('??? @&%#', "Return true on no slugify-text."));
    $this->assertTrue($app->ValidContent->validateSlug('test_2000', "Return false on slugify-text."));
  }
}