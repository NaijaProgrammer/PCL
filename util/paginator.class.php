<?php
class Paginator
{
	private $res_per_page           = 0;
 	private $sql_query_string       = ''; //the sql query (to) execute(d)
 	private $sql_query_string_count = 0;
 	private $url                    = ''; //the current url
 	private $qs                     = ''; //the query string after the url, e.g: "?m=m"
 	private $par_id                 = '';
 	private $max_visible_links      = 0;

	private $num_of_pages           = 1;
	private $current_query_resource = '';
	private $links                  = '';
	
	/**
	* callback function that processes the results
	*/
	private $results_processor = '';

	public function __construct($params)
	{
		$this->res_per_page           = $params['res_per_page'];
 		$this->sql_query_string       = $params['sql_query_string'];
 		$this->sql_query_string_count = $params['sql_query_count'];
 		$this->url                    = $params['url'];
 		$this->qs                     = $params['qs'];
 		$this->par_id                 = $params['par_id'];
 		$this->max_visible_links      = $params['max_visible_links'];
		
		$this->results_processor      = $params['results_processor'];
		
		$this->init();
	}
	
	public function get_data()
	{
		$processor_function = $this->results_processor;
		return array
		(
			'processed_data'  => $processor_function( array('query_string'=>$this->get_query_string()) ),
			'paginated_links' => $this->get_links(),
			'number_of_pages' => $this->get_number_of_pages(),
			'query_string'    => $this->get_query_string() 
		);
	}

	public function get_links()
	{
		return $this->links;
	}

	public function get_number_of_pages()
	{
		return $this->num_of_pages;
	}

	public function get_current_query_resource()
	{
		return $this->current_query_resource;
	}

	public function get_url($full = false)
	{
		return ( ($full) ? $this->url. $this->qs : $this->url );
	}
	
	public function get_query_string()
	{
		return $this->qs;
	}

	protected function init()
	{
		$res_per_page = $this->res_per_page;
		$this->determine_number_of_pages();

   		$sql  = $this->sql_query_string;
   		$sql .= ( ($res_per_page) ? ' LIMIT '. $this->get_start_limit(). ', '. $res_per_page : '' );

		$this->links                  = $this->paginate();
		$this->current_query_resource = mysql_query($sql);
	}

	protected function determine_number_of_pages()
	{
   		if (isset($_GET['num_pages']) && is_numeric($_GET['num_pages']) && ($_GET['num_pages'] > 0) ) 
		{ // Already been determined
   			$this->num_of_pages = $_GET['num_pages'];
   		} 

   		else 
		{
      		if ($this->sql_query_string_count > $this->res_per_page)
			{ // More than 1 page.
       			$this->num_of_pages = ceil ($this->sql_query_string_count / $this->res_per_page);
      		} 
      		else
			{
      			$this->num_of_pages = 1;
      		}
   		}
	}

	protected function paginate()
	{
		$qs               = $this->get_query_string();
		$url              = $this->get_url();
		$num_pages        = $this->get_number_of_pages();
		$start_limit      = $this->get_start_limit();
		$res_per_page     = $this->res_per_page;
		$current_page     = ($start_limit / $res_per_page) + 1;
		$num_lnx_per_page = $this->max_visible_links;
        
		$pages_par      = '<p id="'. $this->par_id. '">';
		$pages_par     .= $this->get_previous_button($current_page); 
		$numbered_pages = '';

		if ( $num_pages == 1 )
		{
			return '';
		}
      		
      		//Make all the numbered pages
      		for ($i = 1; $i <= $num_pages; $i++)
			{  
       			$linked_page   = $url. $qs.'&start_limit='. (($res_per_page * ($i - 1))). '&num_pages='. $num_pages;
			//$linked_page   = $url. '&start_limit='. (($res_per_page * ($i - 1))). '&num_pages='. $num_pages;
       			$inserted_link = $this->get_link_number_button($current_page, $i, $linked_page). ' ';
       
       			$link_array[$i] = $inserted_link;
      		} 
      
      		$numbered_pages .= $this->num_links_per_page($current_page, $num_lnx_per_page, $num_pages, $link_array);
      		$pages_par      .= $numbered_pages; //concatenate the numbered pages to the return paragraph which will be displayed to the user
       		$pages_par      .= $this->get_next_button($current_page);
      		$pages_par.= '</p>';

		return $pages_par;
	}

	protected function get_start_limit()
	{
		if(isset($_GET['start_limit']) && is_numeric($_GET['start_limit']) && ($_GET['start_limit'] > 0) )
		{ // Determine where in the database to start returning results...
    		return $_GET['start_limit'];
   		} 
   		else
		{
    		return  0;
   		}
	}

	protected function get_next_button($current_page)
	{
		if ($current_page == $this->get_number_of_pages())
		{ 
			return '<span id="next" class="pages_no_link">Next</span>';
      	}
		
		$num_pages = $this->get_number_of_pages();

		// If it's not the last page, make a Next button
      	return '<a id="next" href="'. $this->get_url(true). '&start_limit='. ($this->get_start_limit() + $this->res_per_page). '&num_pages='. $num_pages. '">Next</a>';
	}

	protected function get_previous_button($current_page)
	{
		if($current_page == 1)
		{
       		return '<span id="prev" class="pages_no_link prev_no_link">Prev</span>';
      	}
		
		$num_pages = $this->get_number_of_pages();

		// If it's not the first page, make a 'Previous' link
       	return '<a id="prev" href="'. $this->get_url(true). '&start_limit='. ($this->get_start_limit() - $this->res_per_page). '&num_pages='. $num_pages. '">Prev</a>';
	}

	protected function get_link_number_button($current_page_num, $link_num, $linked_page)
	{
   		if($link_num != $current_page_num)
		{
    		return '<a href="'. $linked_page. '">'. $link_num. '</a>';
   		}
  		else
		{
    		return '<span class="pages_no_link">'. $link_num. '</span>';
   		}
	}

	
	protected function num_links_per_page($current_page, $num_links_to_show, $total_num_pages, $link_array)
	{
   		if($num_links_to_show >= $total_num_pages)
		{
			$num_links_to_show = $total_num_pages;
		}

   		if($current_page == 1)
		{
    		$str = $link_array[$current_page];

      		for($i = $current_page + 1; $i <= $num_links_to_show; $i++)
			{
       			$str .= $link_array[$i];
      		}
   		}
   		else if($current_page == $total_num_pages)
		{ 
    		$start_indx = ($total_num_pages - $num_links_to_show) + 1;
    		$str        = $link_array[$start_indx];

      		for($i = $start_indx+1 ;  $i <= $total_num_pages; $i++)
			{
       			$str .= $link_array[$i];
      		}
   		}
   		else
		{
    		$lower_str             = '';
    		$upper_str             = '';
    		$lower_str_upper_limit = $current_page - 1;
    		$upper_str_start_indx  = $current_page + 1;
    		$num_pages_before = $lower_str_upper_limit;
    		$num_pages_after  = $total_num_pages - $current_page;

      		if($current_page < $num_links_to_show)
			{
       			$lower_str_start_indx  = ( ($lower_str_upper_limit == 1) ? 1 : $lower_str_upper_limit - 1); 
       			$disp_pages_before     = $current_page - $lower_str_start_indx;  
       			$disp_pages_after      = $num_links_to_show - $disp_pages_before - 1;
       			$loop_end_limit        = $current_page + $disp_pages_after;
      		}
      		else if($current_page == $num_links_to_show)
			{
       			$lower_str_start_indx = ceil($num_pages_before / 2) + 1;
       			$loop_end_limit       = ($num_links_to_show + $lower_str_start_indx) - 1;
      		}
      		else if($current_page > $num_links_to_show)
			{
         		if($this->is_even($num_links_to_show))
				{
          			$disp_pages_before    = ceil($num_links_to_show / 2) - 1;
          			$disp_pages_after     = $disp_pages_before + 1;
         		}
         		else
				{
          			$disp_pages_before = $disp_pages_after = ($num_links_to_show - 1) / 2; 
         		}

         		$loop_end_limit = $current_page + $disp_pages_after;

         		if($loop_end_limit > $total_num_pages)
				{
          			$loop_end_limit    = $total_num_pages;
          			$disp_pages_after  = $total_num_pages - $current_page;
          			$disp_pages_before = $num_links_to_show - $disp_pages_after - 1;  
         		}

        		$lower_str_start_indx = $current_page - $disp_pages_before;
      		} 
      
      		for($i = $lower_str_start_indx; $i <= $lower_str_upper_limit; $i++ )
			{
       			$lower_str .= $link_array[$i];
      		}

      		for($j = $upper_str_start_indx; $j <= $loop_end_limit; $j++)
			{
       			$upper_str .= $link_array[$upper_str_start_indx++];
     		}

    		$str = $lower_str. $link_array[$current_page]. $upper_str;
   		}

 		return $str; 
	}

	private function is_even($num)
	{
 		return ($num % 2 == 0);
	}
}
