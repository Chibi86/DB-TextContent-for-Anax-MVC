<?php
namespace Chp\TextContent;
/**
 * A test class
 *
 */
class BlogControllerTest extends \PHPUnit_Framework_TestCase
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
    $this->app->BlogController = new \Chp\TextContent\BlogController();
    $this->app->BlogController->setDI($this->di);
    $this->app->BlogController->initialize();
  }
  
  /**
   * Restore or setup database
   *
   */
  private function setupDB(){
    $this->app->db->dropTableIfExists('content')->execute();

    $this->app->db->createTable(
      'content',[
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
    ])->execute();
    
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
      'welcome2',
      NULL,
      'blog-post',
      'Welcome to your new blog2',
      'Example text2...',
      'This is example-blogg to show your new blog system2!',
      'bbcode',
      1,
      $now,
      $now
    ]);
    
    $this->app->db->dropTableIfExists('content_tags')->execute();
    
    $this->app->db->createTable(
      'content_tags',[
        'idContent' => ['integer', 'not null'],
        'tag'       => ['varchar(150)', 'not null'],
        'slug'      => ['varchar(150)', 'not null']
      ])->execute();
    
    $this->app->db->insert(
      'content_tags',
      ['idContent', 'tag', 'slug']
    );
    
    $this->app->db->execute([
      1,
      'New blog',
      'new_blog'
    ]);
    
    $this->app->db->execute([
      2,
      'New blog2',
      'new_blog2'
    ]);
    
    $this->app->db->execute([
      3,
      '<script>abc</script>',
      '_script_abc_script_'
    ]);
    
    $this->app->db->createTable(
      'user',
      [
        'id'       => ['integer', 'primary key', 'not null', 'auto_increment'],
        'acronym'  => ['varchar(20)', 'unique', 'not null'],
        'email'    => ['varchar(80)'],
        'name'     => ['varchar(80)'],
        'password' => ['varchar(255)'],
        'rank'     => ['integer(1)', 'NOT NULL', 'DEFAULT 1'],
        'online'   => ['integer(1)', 'NOT NULL', 'DEFAULT 0'],
        'homepage' => ['varchar(80)'],
        'created'  => ['datetime'],
        'updated'  => ['datetime'],
        'deleted'  => ['datetime'],
        'active'   => ['datetime']
      ]
    )->execute();
  }
  
  /**
   * Testcase get blog posts by tag and type
   *
   */
  public function testGetBlogContent(){
    $result = count($this->app->BlogController->getBlogContent(null, 'new_blog3'));
    $this->assertEquals($result, 0, "Asked for blog posts on none existing tag 'new_blog3', return {$result}.");
    $result = count($this->app->BlogController->getBlogContent(null, 'new_blog'));
    $this->assertEquals($result, 1, "Asked for blog posts on existing tag 'new_blog', should be 1. Return {$result}.");
    $result = count($this->app->BlogController->getBlogContent());
    $this->assertEquals($result, 2, "Asked for all existing blog post, should be 2. Return {$result}.");
  }
  
  /**
   * Testcase prepare post to show in view
   *
   */
  public function testPreparePost(){
    try{
      $this->app->BlogController->preparePost(null);
    }
    catch(Exception $e){
      $this->assertTrue(false, "Code fail when try to prepare null! Caught exception: {$e}.");
    }
    
    try{
      $this->app->BlogController->preparePost((object)[]);
    }
    catch(Exception $e){
      $this->assertTrue(false, "Code fail when try to prepare empty object! Caught exception: {$e}.");
    }
    
    $post = (object)[
      'title'      => '<script>abc</script>',
      'ingress'    => '<script>abc</script>',
      'text'       => '<script>abc</script>',
      'filters'    => 'bbcode',
      'id'         => 3,
      'slug'       => '_script_abc_script_'
    ];
         
    $result = $this->app->BlogController->preparePost($post);
    
    $this->assertNotEquals($result->title, $post->title, "Blog post title should be htmlentities, but the result is the same.");
    $this->assertNotEquals($result->ingress, $post->ingress, "Blog post ingress should be htmlentities, but the result is the same.");
    $this->assertNotEquals($result->text, $post->text, "Blog post text should be htmlentities, but the result is the same.");
    $this->assertTrue(property_exists($result, 'editUrl'), "Blog post edit url should exist!");
    $this->assertTrue(property_exists($result, 'showUrl'), "Blog post show url should exist!");
    
    $tags_result = count($result->tags);
    $this->assertEquals($tags_result, 1, "Blog post should have a tag, return {$tags_result}!");
    if($tags_result > 0)
      $this->assertNotEquals($result->tags[0], "<script>abc</script>", "Blog post tag should be htmlentities, but the result is the same.");
    
    $post->text = "[b]abc[/b]";
    
    $result = $this->app->BlogController->preparePost($post);
    
    $this->assertNotEquals($result->text, $post->text, "Blog post text should be filter with bbcode, but the result is the same.");
  }
}