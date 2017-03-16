<?php
namespace Chp\TextContent;

class TestForm {
  private $values = [
    'id'           => 1,
    'slug'         => '<script>abc</script>',
    'url'          => '<script>abc</script>',
    'title'        => '<script>abc</script>',
    'ingress'      => '<script>abc</script>',
    'text'         => '<script>abc</script>',
    'type'         => 'blog-post',
    'filter'       => array('bbcode','nl2br'),
    'published'    => '1986-08-08 00:00:00',
    'publishedNow' => 'yes'
  ];
  
  public function SetValue($key, $value){
    if(array_key_exists($key, $this->values))
      $this->values[$key] = $value;
  }
  
  public function Value($key){
    if(array_key_exists($key, $this->values))
      return $this->values[$key];
    
    return null;
  }
}

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
    $this->di->setShared('db', function() {
      $db = new \Mos\Database\CDatabaseBasic();
      $db->setOptions(['dsn' => "sqlite::memory:", "verbose" => false, 'table_prefix' => ""]);
      $db->connect();
      
      return $db;
    });
    
    $this->di->setShared('session', function () {
        $session = new \Anax\Session\CSession();
        $session->configure(ANAX_APP_PATH . 'config/session.php');
        //$session->start();
        return $session;
    });
    
    /*$this->di->setShared('user', function () {
        $user = new \Anax\Users\UserSession();
        $user->setDI($this->di);
        return $user;
    });*/
    
    $this->initializeApp();
    $this->setupDB();
  }
  
  /**
   * Helper function to initialize $di and $app
   *
   */
  private function initializeApp()
  {
    $this->app->ContentController = new \Chp\TextContent\ContentController();
    $this->app->ContentController->setDI($this->di);
    $this->app->ContentController->initialize();
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
   * Test prepare contents for show in list view
   *
   */
  public function testPrepareListContent(){
    $contents = [
      (Object)[
        'type'      => 'blog-post',
        'title'     => '<script>abc</script>',
        'id'        => 1,
        'slug'      => '_script_abc_script_',
        'published' => '1986-08-08 00:00:00'
      ]
    ];
    
    $result = $this->app->ContentController->prepareListContent($contents);
    
    $this->assertNotEquals($result[0]->title, $contents[0]->title, "Content title should be htmlentities, but the result is the same.");
    $this->assertTrue(property_exists($result[0], 'typeTxt'), "Content type text should exist!");
    $this->assertTrue(property_exists($result[0], 'editUrl'), "Content edit url should exist!");
    $this->assertTrue(property_exists($result[0], 'removeUrl'), "Content remove url should exist!");
    $this->assertTrue(property_exists($result[0], 'showUrl'), "Content show url should exist!");
    $this->assertTrue(property_exists($result[0], 'available'), "Content available status should exist!");
    
    if(property_exists($result[0], 'available'))
      $this->assertEquals($result[0]->available, 'published', "Content should be available, but status says it's not!");
    
    $contents[0]->type = 'page';
    $contents[0]->url  = '_script_abc_script_';
    $contents[0]->published = '9999-12-31 23:59:59';
    
    $result = $this->app->ContentController->prepareListContent($contents);
    
    $this->assertTrue(property_exists($result[0], 'typeTxt'), "Content type text should exist!");
    $this->assertTrue(property_exists($result[0], 'showUrl'), "Content show url should exist!");
    $this->assertTrue(property_exists($result[0], 'available'), "Content available status should exist!");
    
    if(property_exists($result[0], 'available'))
      $this->assertNotEquals($result[0]->available, 'published', "Content should not be available, but status says it's!");
  }
  
  /**
   * Testcase prepare save of content to database
   *
   */  
  public function testPrepareSaveContent(){
    $now = date('Y-m-d H:i:s');
    
    // Make a test form object
    $form = new TestForm();
    
    $result = $this->app->ContentController->prepareSaveContent($form, 'script_abc_script');
    
    $this->assertNotEquals($result['slug'], $form->Value('slug'), "Content slug should be slugify, but the result is the same.");
    $this->assertNotEquals($result['published'], $form->Value('published'), "Content published should be '{$now}', but the result is the same.");
    $this->assertTrue(array_key_exists('id', $result), "Content index should exist!");
    $this->assertTrue(array_key_exists('updated', $result), "Content updated should exist!");
    
    $this->app->session->set("id", 1);
    $form->SetValue('id', 0);
    $form->SetValue('slug', 'welcome');
    $form->SetValue('publishedNow', 'no');
    
    $result = $this->app->ContentController->prepareSaveContent($form, '');
    
    $this->assertEquals($result['published'], $form->Value('published'), "Content published should be the same, but the result is {$result['published']}.");
    $this->assertNotEquals($result['slug'], $form->Value('slug'), "Content slug should be modify to a unique, but the result is the same.");
    $this->assertTrue(array_key_exists('created', $result), "Content created should exist!");
    $this->assertTrue(array_key_exists('author', $result), "Content author should exist!");
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
   * Test-case check date time
   *
   */
  public function testCheckDatetime(){
    $app = $this->app;
		
    $this->assertNotTrue($app->ContentController->checkDatetime('abc'), "Return true on 'abc' is date/time."); 
    $this->assertTrue($app->ContentController->checkDatetime('1986-08-08 08:08:08'), "Return false on '1986-08-08 08:08:08' is date/time.");
    $this->assertTrue($app->ContentController->checkDatetime(''), "Return false on empty string, should validate true (Empty is acceptable).");
  }
  
  /**
   * Test-case validate filters so they exist
   *
   */
  public function testCheckFilter(){
    $app = $this->app;
		
    $this->assertTrue($app->ContentController->checkFilter(), "Return false on no filter.");
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