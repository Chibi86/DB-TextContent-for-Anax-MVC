<?php
namespace Chp\TextContent;

include_once(__DIR__ . '/../../app/config/text-content.php');

/**
 * Model for text content
 * Made by Rasmus Berg (c) 2014-2017
 *
 * @property  object  $db         PDO database-handler class
 */
Class Content extends \Anax\MVC\CDatabaseModel
{
	/**
	 * Get "all" content from database (limit by per page)
	 *
	 * @param  	int	   		$page				Wich page paging are at
	 * @param  	int	   		$perPage		Contents per page
   * @param  	boolean		$publish 		If it needs to be publish
	 * @return 	object   							With "all" database content data
	 */
	public function getAllContent($page = null, $perPage = null, $publish = true){
    $now    = date('Y-m-d H:i:s');
    $params = array();
    
    $this->db->select("c.*")
             ->from("content AS c")
    //       ->join("user AS u", "c.author = u.id")
             ->where("c.`deleted` IS NULL");
    
    if($publish){
      $this->db->andWhere("c.published <= ?");
      $params[] = $now;
    }
    
    $this->db->orderBy('c.published DESC, c.created DESC');
    
    /*if($perPage){
      $this->db->limit($perPage);
      
      if($page){
        $offset = $this->getOffset($perPage, $page);
        $this->db->offset($offset);
      }
    }*/
    
    $this->db->execute($params);
    
    $this->db->setFetchModeClass(__CLASS__);
    return $this->db->fetchAll();
		
	}
	
	/**
	 * Get "all" content of one type from database (limit by per page)
	 *
	 * @param  	boolean 	$publish 	If it needs to be publish
	 * @param  	string  	$type			Wich type of content ex. blog, page etc.
	 * @param  	int	      $page			Wich page paging are at
	 * @param  	int       $perPage	Contents per page
	 * @return 	object   						With "all" database content data
	 */
	public function getAllContentOfType($type, $page = null, $perPage = null, $publish = true){
    $now    = date('Y-m-d H:i:s');
    $params = array($type);
             
    $this->db->select("c.*")
             ->from("content AS c")
    //       ->join("user AS u", "c.author = u.id")
             ->where("c.`type` = ?")
             ->andWhere("c.`deleted` IS NULL");
    
    if($publish){
      $this->db->andWhere("c.published <= ?");
      $params[] = $now;
    }
    
    $this->db->orderBy('c.published DESC, c.created DESC');
    
    /*if($perPage){
      $this->db->limit($perPage);
      
      if($page){
        $offset = $this->getOffset($perPage, $page);
        $this->db->offset($offset);
      }
    }*/
    
    $this->db->execute($params);
    
    $this->db->setFetchModeClass(__CLASS__);
    return $this->db->fetchAll();
	}
	
	/**
	 * Get "all" content of one tag and one type from database
	 *
	 * @param  	string  	$tag				Wich tag content should have ex. page-news etc.
	 * @param  	string  	$type				Wich type of content ex. blog, page etc.
	 * @param  	boolean 	$publish 		If it needs to be publish
	 * @param  	int   	  $page				Wich page paging are at
	 * @param  	int	      $perPage		Content per page
	 * @return 	object   							With content data from database
	 */
	public function getAllContentOfTag($tag, $type, $page = 0, $perPage = null, $publish = true){
		$now    = date('Y-m-d H:i:s');
    $params = array($tag, $type);
		
    $this->db->select("c.*")
             ->from("content AS c")
    //       ->join("user AS u", "c.author = u.id")
             ->join("content_tags AS t", "t.idContent = c.id")
             ->where("t.slug = ?")
             ->andWhere("c.`type` = ?")
             ->andWhere("c.`deleted` IS NULL");
    
    if($publish){
      $this->db->andWhere("c.published <= ?");
      $params[] = $now;
    }
    
    $this->db->orderBy('c.published DESC, c.created DESC');
    
    /*if($perPage){
      $this->db->limit($perPage);
      
      if($page){
        $offset = $this->getOffset($perPage, $page);
        $this->db->offset($offset);
      }
    }*/
    
    $this->db->execute($params);
    
    $this->db->setFetchModeClass(__CLASS__);
    return $this->db->fetchAll();
	}
	
	/**
	 * Get content from database by url
	 *
	 * @param  	string		$url			Begin or whole url
	 * @param  	boolean 	$publish 	If it needs to be publish
	 * @param  	string  	$type			Wich type of content ex. blog, page etc.
	 * @return 	object   						With content data from database
	 */
	public function getContentByUrl($url, $type, $publish = true){
		$now    = date('Y-m-d H:i:s');
    $params = [$url, $type];
    
    $this->db->select("c.*")
             ->from("content AS c")
    //       ->join("user AS u", "c.author = u.id")
             ->where("c.url = ?")
             ->andWhere("c.`type` = ?")
             ->andWhere("c.`deleted` IS NULL");
    
    if($publish){
      $this->db->andWhere("c.published <= ?");
      $params[] = $now;
    }
    
    $this->db->orderBy('c.published DESC, c.created DESC')
             ->limit(1)
             ->execute($params);
    
    $this->db->setFetchModeClass(__CLASS__);
    return $this->db->fetchOne();
	}
	
	/**
	 * Get content from database by slug
	 *
	 * @param  	string  $slug    	Unique query to content
	 * @param  	boolean $publish 	if it needs to be publish
	 * @param  	string  $type			Wich type of content ex. blog, page etc.
	 * @return 	object   					With content data from database
	 */
	public function getContentBySlug($slug, $type, $publish = true){
    $now = date('Y-m-d H:i:s');
    $params = array($slug, $type);
    $this->db->select("c.*")
             ->from("content AS c")
    //       ->join("user AS u", "c.author = u.id")
             ->where("c.slug = ?")
             ->andWhere("c.`type` = ?")
             ->andWhere("c.`deleted` IS NULL");
             
    if($publish){
      $this->db->andWhere("c.published <= ?");
      $params[] = $now;
    }
    
    $this->db->orderBy('c.published DESC, c.created DESC')
             ->limit(1)
             ->execute($params);
    
    $this->db->setFetchModeClass(__CLASS__);
    return $this->db->fetchOne();
	}
	
	/**
	 * Get content from database by index
	 *
	 * @param  	int       $id				Index to content
	 * @param  	boolean		$publish 	If it needs to be publish
	 * @return 	object   					 	With content data.
	 */
	public function getContentById($id, $publish = true){
    $now    = date('Y-m-d H:i:s');
    $params = array($id);
    
    $this->db->select("c.*, group_concat(t.tag, ', ') AS tags")
             ->from("content AS c")
    //       ->join("user AS u", "c.author = u.id")
             ->leftJoin("content_tags AS t", "t.idContent = c.id")
             ->where("c.id = ?")
             ->andWhere("c.`deleted` IS NULL");
    
    if($publish){
      $this->db->andWhere("c.published <= ?");
      $params[] = $now;
    }
    
    $this->db->execute($params);
    $this->db->setFetchModeClass(__CLASS__);
    return $this->db->fetchOne();
	}
	
	/*****
	 ***
	 ** GET - Counting prepare for Paging (exemple on pagecontrollers blog.php, page.php, report.php and list_content.php)
	 ***
	 *****/
   
	/**
	 * Count all content from database
	 *
	 * @param  	boolean 	$publish 	  If it needs to be publish
	 * @return 	int   					      With count of content in database
	 */
	public function countAllContent($publish = true){
    $now    = date('Y-m-d H:i:s');
    $params = [];
    
    $this->db->select("COUNT(id) AS countAll")
             ->from("Content")
             ->where("`deleted` IS NULL");
    
    if($publish){
      $this->db->andWhere("published <= ?");
      $params[] = $now;
    }
    
    $this->db->execute($params);
    
    $this->db->setFetchModeClass(__CLASS__);
    $count = $this->db->fetchOne();
    return $count->countAll;
	}
	
	/**
	 * Count all content of one type from database
	 *
	 * @param  	boolean 	$publish 	  If it needs to be publish
	 * @param  	string  	$type			  Wich type of content ex. blog, page etc.
	 * @return 	int   			    		  With count of content in database
	 */
	public function countAllContentOfType($type, $publish = true){
    $now    = date('Y-m-d H:i:s');
    $params = [$type];
    
    $this->db->select("COUNT(id) AS countAll")
             ->from("Content")
             ->where("`type` = ?")
             ->andWhere("`deleted` IS NULL");
    
    if($publish){
      $this->db->andWhere("published <= ?");
      $params[] = $now;
    }
    
    $this->db->execute($params);
    
    $this->db->setFetchModeClass(__CLASS__);
    $count = $this->db->fetchOne();
    return $count->countAll;
	}
	
	/**
	 * Count all content of one tag and one type from database
	 *
	 * @param  string     $tag			  Wich tag of content ex. page-news etc.
	 * @param  string     $type		    Wich type of content ex. blog, page etc.
	 * @param  boolean    $publish    Check if it needs to be publish
	 * @return int   						      With count
	 */
	public function countAllContentOfTag($tag, $type, $publish = true){
    $now    = date('Y-m-d H:i:s');
    $params = [$tag, $type];
    
    $this->db->select("COUNT(c.id) AS countAll")
             ->from("content AS c")
             ->join("content_tags AS t", "t.idContent = c.id")
             ->where("t.slug = ?")
             ->andWhere("c.`type` = ?")
             ->andWhere("c.`deleted` IS NULL");
    
    if($publish){
      $this->db->andWhere("c.published <= ?");
      $params[] = $now;
    }
    
    $this->db->execute($params);
    
    $this->db->setFetchModeClass(__CLASS__);
    $count = $this->db->fetchOne();
    
    return $count->countAll;		
	}
	
	/*****
	 ***
	 ** GET - Help functions (exemple on pagecontrollers blog.php, page.php, report.php and list_content.php)
	 ***
	 *****/
   
	/**
	 * Get tags for a content
	 *
	 * @param		int     $id		  Index to content
	 * @return	object	$res	  Tags result
	 */
	public function getTagsForContent($id){
		$this->db->select("tag, slug")
             ->from("content_tags")
             ->where("idContent = ?")
             ->execute(array($id));
    
    $this->db->setFetchModeClass(__CLASS__);
    return $this->db->fetchAll();
	}
	
	/**
	 * Get a tag's title by slug
	 *
	 * @param		string	  $slug		  URL for tag
	 * @return	string					    Tag-title from database
	 */
	public function getTagBySlug($slug){
		$this->db->select("tag")
             ->from("content_tags")
             ->where("slug = ?")
             ->execute(array($slug));
    
    $this->db->setFetchModeClass(__CLASS__);
    $tag = $this->db->fetchOne();
    
    if(isset($tag->tag))
      return 	$tag->tag;
    
    return null;
	}
  
  /**
   * Check if slug is available, or if not get one that is
   *
   * @param   string    $slug       Slug to validate
   * @param   string    $type       Type of content
   * @return  string    $newSlug    A available slug
   */
  public function makeSlugToContent($slug, $type){
    $newSlug = $slug;
    $j = 1;
    
    do{
      if($j > 1)
        $newSlug = "{$slug}_{$j}";
      
      $this->db->select("slug")
               ->from("content")
               ->where("slug = ?")
               ->andWhere("type = ?")
               ->andWhere("deleted IS NULL")
               ->execute(array($newSlug, $type));
      
      $this->db->setFetchModeClass(__CLASS__);
      $slugObj = $this->db->fetchOne();
      
      $j++;
    }while($slugObj);
    
    return $newSlug;
  }
  
  /**
   * Get sql-query offset by per page and page
   *
   *  @param    int   $perPage    Per page
   *  @param    int   $page       Page on paging
   *  @return   int   $offset     Offset for sql-query
   */
  public function getOffset($perPage, $page){
    $offset = null;
    if($perPage){
			$offset = ($perPage * ($page - 1));
			$offset = ($offset > 0) ? $offset : 0;
		}
    
    return $offset;
  }
}