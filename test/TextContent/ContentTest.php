<?php
namespace Chp\TextContent;
/**
 * A test class
 *
 */
class ContentTest extends \PHPUnit_Framework_TestCase
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
		$this->di->setShared('db', function() {
			$db = new \Mos\Database\CDatabaseBasic();
			$db->setOptions(['dsn' => "sqlite::memory:", "verbose" => false, 'table_prefix' => ""]);
			$db->connect();
      
      return $db;
    });
    
    $this->initializeApp();
    $this->setupDB();
  }
  
  /**
	 * Helper function to initialize $di and $app
	 *
	 */
	private function initializeApp()
	{
		$this->app->Content = new \Chp\TextContent\Content();
		$this->app->Content->setDI($this->di);
	}
  
  /**
   * Restore or setup database
   *
   */
  private function setupDB(){

    $this->app->db->dropTableIfExists('content')->execute();

    $this->app->db->createTable(
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
    
    $this->app->db->insert(
      'content',
      ['slug', 'url', 'type', 'title', 'ingress', 'text', 'filters', 'author', 'published', 'created']
    );
    
    $now = date('Y-m-d H:i:s');
    
    $this->app->db->execute([
      'welcome',
      NULL,
      'blog-post',
      'Welcome to your new blog',
      'Example text...',
      'This is example-blogg to show your new blog system!',
      'bbcode',
      1,
      $now,
      $now
    ]);
    
    $this->app->db->execute([
      'welcome',
      'example-page',
      'page',
      'Welcome to your new page',
      'Example text...',
      'This is example-page to show your new page system!',
      'bbcode',
      1,
      $now,
      $now
    ]);
    
    $this->app->db->execute([
      'welcome',
      'example-page2',
      'page',
      'Welcome to your new page',
      'Example text...',
      'This is example-page to show your new page system!',
      'bbcode',
      1,
      '9999-01-31 23:59:59',
      $now
    ]);
    
    $this->app->db->dropTableIfExists('content_tags')->execute();
    
    $this->app->db->createTable(
      'content_tags',
      [
        'idContent' => ['integer', 'not null'],
        'tag'       => ['varchar(150)', 'not null'],
        'slug'      => ['varchar(150)', 'not null']
      ]
    )->execute();
    
    $this->app->db->insert(
      'content_tags',
      ['idContent', 'tag', 'slug']
    );
    
    $this->app->db->execute([
      1,
      'New blog',
      'new_blog'
    ]);
  }
  
  /**
   * Test-case get all content
   *
   */
  public function testGetAllContent(){
    $app = $this->app;
    
    $result = $app->Content->getAllContent(null, null, false);
    $this->assertTrue(!empty($result), "Searching all content (existing), find no content.");
    
    $result = $app->Content->getAllContent();
    $this->assertTrue(!empty($result), "Searching published content (existing), find no content.");
  }
  
  /**
   * Test-case get all content of type
   *
   */
  public function testGetAllContentOfType(){
    $app = $this->app;
    
    $result = $app->Content->getAllContentOfType('test', null, null, false);
    $this->assertTrue(empty($result), "Asking for all content of no existing type, find content.");
    
    $result = $app->Content->getAllContentOfType('blog-post', null, null, false);
    $this->assertTrue(!empty($result), "Asking for all content of type 'blog-post' (existing), find no content.");
    
    $result = $app->Content->getAllContentOfType('blog-post');
    $this->assertTrue(!empty($result), "Asking for published content of type 'blog-post' (existing), find no content.");
  }
  
  /**
   * Test-case get all content of tag
   *
   */
  public function testGetAllContentOfTag(){
    $app = $this->app;
    
    $result = $app->Content->getAllContentOfTag('test', 'blog-post', null, null, false);
    $this->assertTrue(empty($result), "Asking for all content of no existing tag, find content.");
    
    $result = $app->Content->getAllContentOfTag('new_blog', 'test', null, null, false);
    $this->assertTrue(empty($result), "Asking for all content of no existing type, find content.");
    
    $result = $app->Content->getAllContentOfTag('new_blog', 'blog-post', null, null, false);
    $this->assertTrue(!empty($result), "Asking for all content of tag 'new_blog' (existing), find no content.");
    
    $result = $app->Content->getAllContentOfTag('new_blog', 'blog-post');
    $this->assertTrue(!empty($result), "Asking for published content of tag 'new_blog' (existing), find no content.");
  }
  
  /**
   * Test-case get content by url
   *
   */
  public function testGetContentByUrl(){
    $app = $this->app;
    
    $result = $app->Content->getContentByUrl('test', 'page', false);
    $this->assertTrue(empty($result), "Asking for content of no existing url, find content.");
    
    $result = $app->Content->getContentByUrl('example-page', 'test', false);
    $this->assertTrue(empty($result), "Asking for content of no existing type, find content.");
    
    $result = $app->Content->getContentByUrl('example-page', 'page', false);
    $this->assertTrue(!empty($result), "Asking for content for url 'example-page' (existing), find no content.");
    
    $result = $app->Content->getContentByUrl('example-page', 'page');
    $this->assertTrue(!empty($result), "Asking for published content of url 'example-page' (existing), find no content.");
  }
  
  /**
   * Test-case get content by slug
   *
   */
  public function testGetContentBySlug(){
    $app = $this->app;
    
    $result = $app->Content->getContentBySlug('test', 'blog-post', false);
    $this->assertTrue(empty($result), "Asking for content of no existing slug, find content.");
    
    $result = $app->Content->getContentBySlug('welcome', 'test', false);
    $this->assertTrue(empty($result), "Asking for content of no existing type, find content.");
    
    $result = $app->Content->getContentBySlug('welcome', 'blog-post', false);
    $this->assertTrue(!empty($result), "Asking for content for slug 'example-page' (existing), find no content.");
    
    $result = $app->Content->getContentBySlug('welcome', 'blog-post');
    $this->assertTrue(!empty($result), "Asking for published content of slug 'example-page' (existing), find no content.");
  }
  
  /**
   * Test-case get content by id
   *
   */
  public function testGetContentById(){
    $app = $this->app;
    
    $result = $app->Content->getContentById(10, false);
    $this->assertTrue(is_null($result->id), "Asking for content of no existing id, find content.");
    
   $result = $app->Content->getContentById(3, true);
    $this->assertTrue(is_null($result->id), "Asking for published content with id 3 (not published), find content.");
    
    $result = $app->Content->getContentById(1, false);
    $this->assertTrue(!is_null($result->id), "Asking for content of id 1 (existing), find no content.");
  }
  
  /**
   * Test-case count all content
   *
   */
  public function testCountAllContent(){
    $app = $this->app;
    
    $result = $app->Content->countAllContent(false);
    $this->assertTrue(!empty($result), "Asking for count of all content (three), return zero.");
    
    $result = $app->Content->countAllContent();
    $this->assertTrue(!empty($result), "Asking for count of all published content (two), return zero.");
  }
  
  /**
   * Test-case count all content of type
   *
   */
  public function testCountAllContentOfType(){
    $app = $this->app;
    
    $result = $app->Content->countAllContentOfType('test', false);
    $this->assertEquals($result, 0, "Asking for count of all content of no existing type, return {$result}.");
    
    $result = $app->Content->countAllContentOfType('blog-post', false);
    $this->assertTrue($result > 0, "Asking for count of all content of type 'blog-post' (one), return zero.");
    
    $result = $app->Content->countAllContentOfType('page');
    $this->assertTrue($result > 0, "Asking for count of all published content of type 'page' (one), return zero.");
  }
  
  /**
   * Test-case count all content of tag
   *
   */
  public function testCountAllContentOfTag(){
    $app = $this->app;
    
    $result = $app->Content->countAllContentOfTag('test', 'blog-post', false);
    $this->assertEquals($result, 0, "Asking for count of all content of no existing type, return {$result}.");
    
    $result = $app->Content->countAllContentOfTag('new_blog', 'test', false);
    $this->assertEquals($result, 0, "Asking for count of all content of no existing type, return  {$result}.");
     
    $result = $app->Content->countAllContentOfTag('new_blog', 'blog-post', false);
    $this->assertTrue($result > 0, "Asking for count of all content of tag 'new_blog' (one), return zero.");
    
    $result = $app->Content->countAllContentOfTag('new_blog', 'blog-post');
    $this->assertTrue($result > 0, "Asking for count of all published content of tag 'new_blog' (one), return zero.");
  }
 
   /**
    * Test-case get tags for content
    *
    */
  public function testGetTagsForContent(){
    $app = $this->app;
    
    $result = $app->Content->getTagsForContent(4);
    $this->assertTrue(empty($result), "Asking for tags of no existing id, find tags.");
    
    $result = $app->Content->getContentById(1);
    $this->assertTrue(!empty($result), "Asking for tags of id 1 (existing), find no tags.");
  }
  
  /**
   * Test-case get tags for content
   *
   */
  public function testGetTagBySlug(){
    $app = $this->app;
    
    $result = $app->Content->getTagBySlug('test');
    $this->assertNull($result, "Asking for tag of no existing slug, find tag.");
    
    $result = $app->Content->getTagBySlug('new_blog');
    $this->assertNotNull($result, "Asking for tag by tag 'new_blog' (existing), find no tags.");
  }
  
  /**
   * Test-case check if slug is available, or if not get one that is
   *
   */
  public function testMakeSlugToContent(){
    $app = $this->app;
    
    $result = $app->Content->makeSlugToContent('welcome', 'blog-post');
    $this->assertEquals($result, 'welcome_2', "Return slug: {$result}, when it should been 'welcome_2'.");
    
    $result = $app->Content->makeSlugToContent('test', 'blog-post');
    $this->assertEquals($result, 'test', "Return slug: {$result}, when it should been 'test'.");
  }
  
  /**
   * Test-case get offset for sql-query
   *
   */
  public function testGetOffset(){
    $app = $this->app;
    
    $result = $app->Content->getOffset(5, 1);
    $this->assertEquals($result, 0, "Offset should be zero, is {$result}.");
    
    $result = $app->Content->getOffset(5, 2);
    $this->assertEquals($result, 5, "Offset should be five is {$result}.");
  }
}