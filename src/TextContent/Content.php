<?php
namespace Chp\TextContent;

/**
 * Model for text content
 * Made by Rasmus Berg (c) 2014-2016
 */
Class Content extends \Anax\MVC\CDatabaseModel
{
	
	/**
	 * Get "all" content from database (limit by per page)
	 *
	 * @Param  	Integer	   		$page				Wich page paging are at
	 * @Param  	Integer	   		$perPage		Contents per page
   * @Param  	Boolean		    $publish 		If it needs to be publish
	 * @Return 	Object   							    With "all" database content data
	 */
	public function getAllContent($page = null, $perPage = null, $publish = true){
    $now    = date('Y-m-d H:i:s');
    $params = array();
    
    $this->db->select("c.*");
    $this->db->from("content AS c");
    //$this->db->join("user AS u", "c.author = u.id");
    $this->db->where("c.`deleted` IS NULL");
    
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
	 * @Param  	Boolean 	$publish 	If it needs to be publish
	 * @Param  	String  	$type			Wich type of content ex. blog, page etc.
	 * @Param  	Integer	  $page			Wich page paging are at
	 * @Param  	Integer   $perPage	Contents per page
	 * @Return 	Object   						With "all" database content data
	 */
	public function getAllContentOfType($type, $page = null, $perPage = null, $publish = true){
    $now    = date('Y-m-d H:i:s');
    $params = array($type);
             
    $this->db->select("c.*");
    $this->db->from("content AS c");
    //$this->db->join("user AS u", "c.author = u.id");
    $this->db->where("c.`type` = ?");
    $this->db->andWhere("c.`deleted` IS NULL");
    
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
	 * @Param  	String  	$tag				Wich tag content should have ex. page-news etc.
	 * @Param  	String  	$type				Wich type of content ex. blog, page etc.
	 * @Param  	Boolean 	$publish 		If it needs to be publish
	 * @Param  	Integer	  $page				Wich page paging are at
	 * @Param  	Integer	  $perPage		Content per page
	 * @Return 	Object   							With content data from database
	 */
	public function getAllContentOfTag($tag, $type, $page = 0, $perPage = null, $publish = true){
		$now    = date('Y-m-d H:i:s');
    $params = array($tag, $type);
		
    $this->db->select("c.*");
    $this->db->from("content AS c");
    //$this->db->join("user AS u", "c.author = u.id");
    $this->db->join("content_tags AS t", "t.idContent = c.id");
    $this->db->where("t.slug = ?");
    $this->db->andWhere("c.`type` = ?");
    $this->db->andWhere("c.`deleted` IS NULL");
    
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
	 * @Param  	String		$url			Begin or whole url
	 * @Param  	Boolean 	$publish 	If it needs to be publish
	 * @Param  	String  	$type			Wich type of content ex. blog, page etc.
	 * @Return 	Object   						With content data from database
	 */
	public function getContentByUrl($url, $type, $publish = true){
		$now    = date('Y-m-d H:i:s');
    $params = [$url, $type];
    
    $this->db->select("c.*");
    $this->db->from("content AS c");
    //$this->db->join("user AS u", "c.author = u.id");
    $this->db->where("c.url = ?");
    $this->db->andWhere("c.`type` = ?");
    $this->db->andWhere("c.`deleted` IS NULL");
    
    if($publish){
      $this->db->andWhere("c.published <= ?");
      $params[] = $now;
    }
    
    $this->db->orderBy('c.published DESC, c.created DESC');
    $this->db->limit(1);
    $this->db->execute($params);
    
    $this->db->setFetchModeClass(__CLASS__);
    return $this->db->fetchOne();
	}
	
	/**
	 * Get content from database by slug
	 *
	 * @Param  	String  $slug    	Unique query to content
	 * @Param  	Boolean $publish 	if it needs to be publish
	 * @Param  	String  $type			Wich type of content ex. blog, page etc.
	 * @Return 	Object   					With content data from database
	 */
	public function getContentBySlug($slug, $type, $publish = true){
    $now = date('Y-m-d H:i:s');
    $params = array($slug, $type);
    
    $this->db->select("c.*");
    $this->db->from("content AS c");
    //$this->db->join("user AS u", "c.author = u.id");
    $this->db->where("c.slug = ?");
    $this->db->andWhere("c.`type` = ?");
    $this->db->andWhere("c.`deleted` IS NULL");
             
    if($publish){
      $this->db->andWhere("c.published <= ?");
      $params[] = $now;
    }
    
    $this->db->orderBy('c.published DESC, c.created DESC');
    $this->db->limit(1);
    $this->db->execute($params);
    
    $this->db->setFetchModeClass(__CLASS__);
    return $this->db->fetchOne();
	}
	
	/**
	 * Get content from database by index
	 *
	 * @Param  	Integer   $id				Index to content
	 * @Param  	Boolean		$publish 	If it needs to be publish
	 * @Return 	Object   					 	With content data.
	 */
	public function getContentById($id, $publish = true){
    $now    = date('Y-m-d H:i:s');
    $params = array($id);
    
    $this->db->select("c.*, group_concat(t.tag separator ', ') AS tags");
    $this->db->from("content AS c");
    //$this->db->join("user AS u", "c.author = u.id");
    $this->db->join("content_tags AS t", "t.idContent = c.id");
    $this->db->where("c.id = ?");
    $this->db->andWhere("c.`deleted` IS NULL");
    
    if($publish){
      $this->db->andWhere("c.published <= ?");
      $params[] = $now;
    }
    
    $this->db->execute($params);
    
    $this->db->setFetchModeClass(__CLASS__);
    return $this->db->fetchOne();
	}
	
	/**
	 * Count all content from database
	 *
	 * @Param  	Boolean 	$publish 	  If it needs to be publish
	 * @Return 	Integer   					  With count of content in database
	 */
	public function countAllContent($publish = true){
    $now    = date('Y-m-d H:i:s');
    $params = [];
    
    $this->db->select("COUNT(id) AS countAll");
    $this->db->from("Content");
    $this->db->where("`deleted` IS NULL");
    
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
	 * @Param  	Boolean 	$publish 	  If it needs to be publish
	 * @Param  	String  	$type			  Wich type of content ex. blog, page etc.
	 * @Return 	Integer   					  With count of content in database
	 */
	public function countAllContentOfType($type, $publish = true){
    $now    = date('Y-m-d H:i:s');
    $params = [$types];
    
    $this->db->select("COUNT(id) AS countAll");
    $this->db->from("Content");
    $this->db->where("`type` = ?");
    $this->db->andWhere("`deleted` IS NULL");
    
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
	 * @Param  String     $tag			  Wich tag of content ex. page-news etc.
	 * @Param  String     $type		    Wich type of content ex. blog, page etc.
	 * @Param  Boolean    $publish    Check if it needs to be publish
	 * @Return Integer   						  With count
	 */
	public function countAllContentOfTag($tag, $type, $publish = true){
    $now    = date('Y-m-d H:i:s');
    $params = [$tag, $type];
    
    $this->db->select("COUNT(c.id) AS countAll");
    $this->db->from("content AS c");
    $this->db->join("content_tags AS t", "t.idContent = c.id");
    $this->db->where("t.slug = ?");
    $this->db->andWhere("c.`type` = ?");
    $this->db->andWhere("c.`deleted` IS NULL");
    
    if($publish){
      $this->db->andWhere("c.published <= ?");
      $params[] = $now;
    }
    
    $this->db->execute($params);
    
    $this->db->setFetchModeClass(__CLASS__);
    $count = $this->db->fetchOne();
    return $count->countAll;		
	}
	
	/**
	 * Get tags for a content
	 *
	 * @Param		Integer		$id		  Index to content
	 * @Return	Object		$res	  Tags result
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
	 * @Param		String	  $slug		  URL for tag
	 * @Return	String					    Tag-title from database
	 */
	public function getTagBySlug($slug){
		$this->db->select("tag")
             ->from("content_tags")
             ->where("slug = ?")
             ->execute(array($slug));
    
    $this->db->setFetchModeClass(__CLASS__);
    $tag = $this->db->fetchOne();
    return 	$tag->tag;
	}
  
  /**
   * Check if slug is available, or if not get one that is
   *
   * @Param   String    $slug       Slug to validate
   * @Param   String    $type       Type of content
   * @Return  String    $newSlug    A available slug
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
   *  @Param    Integer   $perPage    Per page
   *  @Param    Integer   $page       Page on paging
   *  @Return   Integer   $offset     Offset for sql-query
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
?>