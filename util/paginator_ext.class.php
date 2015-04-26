<?php
/**
* Pagination class
* Making pagination of records easy(ier)
*/
class PaginatorExt
{
	/**
	* The query we will be paginating
	*/
	private $query = "";

	/**
	* The processed query which will be executed / has been executed
	*/
	private $executedQuery = "";
	
	/**
	* The maximum number of results to display per page
	*/
	private $limit = 25;

	/**
	* The results offset - i.e. page we are on (-1)
	*/
	private $offset = 0;

	/**
	* The method of pagination
	*/
	private $method = 'query';

	/**
	* The cache ID if we paginate by caching results
	*/
	private $cache;

	/**
	* The results set if we paginate by executing directly
	*/
	private $results;

	/**
	* The number of rows there were in the query passed
	*/
	private $numRows;

	private $numRowsPage;
	private $numPages;
	private $isFirst;
	private $isLast;

	private $currentPage;
	
	/**
	* database object (added by Michael Orji)
	* so it could work with any database
	*/
	private $db_object; 

	/**
	* Our constructor
	* @param Object registry
	* @return void
	*/
	function __construct( Registry $registry)
	{
		$this->registry = $registry;
	}

	/**
	* Set the query to be paginated
	* @param String $sql the query
	* @return void
	*/
	public function setQuery( $sql )
	{
		$this->query = $sql;
	}

	/**
	* Set the limit of how many results should be displayed per page
	* @param int $limit the limit
	* @return void
	*/
	public function setLimit( $limit )
	{
		$this->limit = $limit;
	}

	/**
	* Set the offset - i.e. if offset is 1, then we show the next page of results
	* @param int $offset the offset
	* @return void
	*/
	public function setOffset( $offset )
	{
		$this->offset = $offset;
	}

	/**
	* Set the method we want to use to paginate
	* @param String $method [cache|execute]
	* @return void
	*/
	public function setMethod( $method )
	{
		$this->method = $method;
	}
	
	public function setDatabaseAccessObject($db_obj)
	{
		$this->db_object = $db_obj;
	}

	/**
	* Process the query, and set the paginated properties
	* @return bool
	*/
	public function generatePagination()
	{
		$temp_query = $this->query;
		$this->db_object->execute_query( $temp_query );
		$this->numRows = $this->db_object->num_rows();

		$limit = " LIMIT ";
		$limit .= ( $this->offset * $this->limit ) . ", " . $this->limit;
		$temp_query = $temp_query . $limit;
		$this->executedQuery = $temp_query;
		if( $this->method == 'cache' )
		{
			$this->cache = $this->db_object->cache_query( 'pagination_query', $temp_query );
		}
		else if( $this->method == 'execute' )
		{
			$this->db_object->execute_query( $temp_query );
			$this->results = $this->db_object->get_rows();
		}
		$this->numPages    = ceil($this->numRows / $this->limit);
		$this->isFirst     = ( $this->offset == 0 ) ? true : false;
		$this->isLast      = ( ( $this->offset + 1 ) == $this->numPages ) ? true : false;
		$this->currentPage = ( $this->numPages == 0 ) ? 0 : $this->offset+1;
		$this->numRowsPage = $this->db_object->num_rows();
		if( $this->numRowsPage == 0 )
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	* Get the cached results
	* @return int
	*/
	public function getCache()
	{
		return $this->cache;
	}
	/**
	* Get the result set
	* @return array
	*/
	public function getResults()
	{
		return $this->results;
	}
	/**
	* Get the number of pages of results there are
	* @return int
	*/
	public function getNumPages()
	{
		return $this->numPages;
	}
	/**
	* Is this page the first page of results?
	* @return bool
	*/
	public function isFirst()
	{
		return $this->isFirst;
	}
	/**
	* Is this page the last page of results?
	* @return bool
	*/
	public function isLast()
	{
		return $this->isLast;
	}
	/**
	* Get the current page within the paginated results we are viewing
	* @return int
	*/
	public function getCurrentPage()
	{
		return $this->currentPage;
	}
}

?>
<?php 

//usage examples: 
/**
* Generate paginated members list
* @param int $offset the offset
* @return Object pagination object
*/
function listMembers( $offset=0 )
{

	$paginatedMembers = new Pagination( $this->registry );
	$paginatedMembers->setLimit( 25 );
	$paginatedMembers->setOffset( $offset );
	$query = "SELECT u.ID, u.username, p.name, p.dino_name,
	p.dino_gender, p.dino_breed FROM users u, profile p WHERE
	p.user_id=u.ID AND u.active=1 AND u.banned=0 AND u.deleted=0";
	$paginatedMembers->setQuery( $query );
	$paginatedMembers->setMethod( 'cache' );
	$paginatedMembers->generatePagination();
	return $paginatedMembers;
}
function listMembersByLetter( $letter='A', $offset=0 )
{
	$alpha = strtoupper( $this->registry->getObject('db')->sanitizeData( $letter ) );
	require_once( FRAMEWORK_PATH .'lib/pagination/pagination.class.php');
	$paginatedMembers = new Pagination( $this->registry );
	$paginatedMembers->setLimit( 25 );
	$paginatedMembers->setOffset( $offset );
	$query = "SELECT u.ID, u.username, p.name, p.dino_name,
	p.dino_gender, p.dino_breed FROM users u, profile p WHERE
	p.user_id=u.ID AND u.active=1 AND u.banned=0 AND u.deleted=0
	AND SUBSTRING_INDEX(p.name,' ', -1)LIKE'".$alpha."%' ORDER BY
	SUBSTRING_INDEX(p.name,' ', -1) ASC";
	$paginatedMembers->setQuery( $query );
	$paginatedMembers->setMethod( 'cache' );
	$paginatedMembers->generatePagination();
	return $paginatedMembers;
}
function filterMembersByName( $filter='', $offset=0 )
{
	$filter = ( $this->registry->getObject('db')->sanitizeData( urldecode( $filter ) ) );
	require_once( FRAMEWORK_PATH .'lib/pagination/pagination.class.php');
	$paginatedMembers = new Pagination( $this->registry );
	$paginatedMembers->setLimit( 25 );
	$paginatedMembers->setOffset( $offset );
	$query = "SELECT u.ID, u.username, p.name, p.dino_name,
	p.dino_gender, p.dino_breed FROM users u, profiles p WHERE
	p.user_id=u.ID AND u.active=1 AND u.banned=0 AND u.deleted=0
	AND p.name LIKE'%".$filter."%' ORDER BY p.name ASC";
	$paginatedMembers->setQuery( $query );
	$paginatedMembers->setMethod( 'cache' );
	$paginatedMembers->generatePagination();
	return $paginatedMembers;
}

function searchMembers( $search=true, $name='', $offset=0 )
{
	require_once( FRAMEWORK_PATH . 'models/members.php');
	$members = new Members( $this->registry );
	if( $search == true )
	{
		// we are performing the search
		$pagination = $members->filterMembersByName( urlencode($_POST['name'] ), $offset );
		$name = urlencode( $_POST['name'] );
	}
	else
	{
		// we are paginating search results
		$pagination = $members->filterMembersByName( $name, $offset );
	}
	if( $pagination->getNumRowsPage() == 0 )
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'members/invalid.tpl.php', 'footer.tpl.php');
	}
	else
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'members/search.tpl.php', 'footer.tpl.php');
		$this->registry->getObject('template')->getPage()->addTag( 'members', array( 'SQL', $pagination->getCache() ) );
		$this->registry->getObject('template')->getPage()->addTag( 'public_name', urldecode( $name ) );
		$this->registry->getObject('template')->getPage()->addTag( 'encoded_name', $name );
		$this->registry->getObject('template')->getPage()->addTag( 'page_number', $pagination->getCurrentPage() );
		$this->registry->getObject('template')->getPage()->addTag( 'num_pages', $pagination->getNumPages() );
		if( $pagination->isFirst() )
		{
			$this->registry->getObject('template')->getPage()->addTag( 'first', '');
			$this->registry->getObject('template')->getPage()->addTag( 'previous', '' );
		}
		else
		{
			$this->registry->getObject('template')->getPage()->addTag( 'first', "<a href='members/search-results/".$name."/'>First page</a>" );
			$this->registry->getObject('template')->getPage()->addTag( 'previous', "<a href='members/search-results/".$name."/" . ( $pagination-getCurrentPage() - 2 ) ."'>Previous page</a>" );
		}
		if( $pagination->isLast() )
		{
			$this->registry->getObject('template')->getPage()->addTag( 'next', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'last', '' );
		}
		else
		{
			$this->registry->getObject('template')->getPage()->addTag( 'first', "<a href='members/search-results/".$name."/" . $pagination->getCurrentPage() ."'>Next page</a>" );
			$this->registry->getObject('template')->getPage()->addTag( 'previous', "<a href='members/search-results/".$name. "/" . ( $pagination->getNumPages() - 1 ) ."'>Last page</a>" );
		}
	}
}
?>