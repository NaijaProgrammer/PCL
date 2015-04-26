<?php

/**
* @author Michael Orji
*/
class SocialSharer
{
	private $share_url; //url of the item you are sharing (article, post, trend, tweet, post, etc)
	private $share_title; //title of the item
	private $share_body; //body of the item. For multimedia elements such as audio or video, this is most commonly an <object> and/or <embed> code
	private $share_networks_list; //list of (social network) sites on which our item will be shared
	private $icon_img_path = '';
	private $icon_img_ext  = '';

	/* 
	* constructor: initialises the url, title and body of the item to share
	* @params    : string url, string title, string body
	* @return    : void;
	*/
	public function __construct($url, $title, $body)
	{
		$this->share_url   = $url;
		$this->share_title = $title;
		$this->share_body  = $body;
		//$this->share_networks();
	}

	public function set_icon_image_params($path, $ext)
	{
		$this->icon_img_path = $path;
		$this->icon_img_ext  = $ext;
	}

	/*
	* creates the list of social network sites where our item will be shared AND
	* stores the result in our share_networks_list property
	* @params: void
	* @return: void
	*/
	protected function share_networks()
	{
		$enc_url   = urlencode($this->share_url);
		$enc_title = urlencode($this->share_title);
		$enc_body  = urlencode($this->share_body);
		$img_path  = $this->icon_img_path;
		$img_ext   = $this->icon_img_ext;
 
		$networks  = "";
		$networks .= "<li><a href='http://www.facebook.com/sharer.php?u=$enc_url&t=$enc_title' title='Facebook' target='_blank'><img src='{$img_path}facebook.{$img_ext}' />Facebook</a></li>";
		#$networks .= "<li><a href='http://twitter.com/home/?status=$enc_title $enc_url' title='Twitter' target='_blank'><img src='{$img_path}twitter.{$img_ext}' />Twitter</a></li>";
		$networks .= "<li><a href='http://twitter.com/share?url=$enc_url&text=$enc_title' title='Twitter' target='_blank'><img src='{$img_path}twitter.{$img_ext}' />Twitter</a></li>";
		$networks .= "<li><a href='http://www.myspace.com/Modules/PostTo/Pages/?l=3&u=$enc_url&t=$enc_title&c=$enc_body' title='MySpace' target='_blank'><img src='{$img_path}myspace.{$img_ext}' />MySpace</a></li>";
		$networks .= "<li><a href='http://www.stumbleupon.com/submit?url=$enc_url&title=$enc_title' title='StumbleUpon' target='_blank'><img src='{$img_path}stumbleupon.{$img_ext}' />StumbleUpon</a></li>";
		$networks .= "<li><a href='http://digg.com/submit?phase=2&url=$enc_url&title=$enc_title' title='Digg' target='_blank'><img src='{$img_path}digg.{$img_ext}' />Digg</a></li>";
		$networks .= "<li><a href='http://reddit.com/submit?url=$enc_url&title=$enc_title' title='Reddit' target='_blank'><img src='{$img_path}reddit.{$img_ext}' />Reddit</a></li>";
		$networks .= "<li><a href='http://del.icio.us/post?url=$enc_url&title=$enc_title' title='del.icio.us' target='_blank'><img src='{$img_path}delicious.{$img_ext}' />del.icio.us</a></li>";
		$networks .= "<li><a href='http://www.orkut.com/FavoriteVideos.aspx?u=$enc_url' title='Orkut' target='_blank'><img src='{$img_path}orkut.{$img_ext}' />Orkut</a></li>";
		$networks .= "<li><a href='http://spaces.live.com/BlogIt.aspx?Title=$enc_title&SourceURL=$enc_url&description=$enc_body' title='Live Spaces' target='_blank'><img src='{$img_path}livespaces.{$img_ext}' />Live Spaces</a></li>";
		#$networks .= "<li><a href='http://www.bebo.com/c/share?Url=$enc_url&Title=$enc_title&TUUID=c583051f-6b2d-41ec-8dd0-a3a0ee1656c1&MID=8348657161' title='Bebo' target='_blank'><img src='{$img_path}bebo.{$img_ext}' />Bebo</a></li>";
		$networks .= "<li><a href='http://www.hi5.com/friend/checkViewedVideo.do?t=$enc_title&url=$enc_url&embeddable=true&simple=true' title='hi5' target='_blank'><img src='{$img_path}hi5.{$img_ext}' />hi5</a></li>";
		#$networks .= "<li><a href='http://www.blogger.com/blog-this.g?n=$enc_title&source=$enc_url&b=$enc_body&eurl=http://i3.ytimg.com/vi/V4KHGhiM1fU/hqdefault.jpg' title='Blogger' target='_blank'><img src='{$img_path}blogger.{$img_ext}' />Blogger</a></li>";
		#google has closed buzz $networks .= "<li><a href='http://www.google.com/buzz/post?url=$enc_url' title='Google Buzz' target='_blank'><img src='{$img_path}buzz.{$img_ext}' />Buzz</a></li>";
		$networks .= "<li><div href='$enc_url' class='g-plusone' data-annotation='inline' data-width='300'></div></li>";
    
		$this->share_networks_list = $networks;
	}

	/*
	* adds a new url to the ones already available in our share_networks_list
	* @param : string the url to add
	* @return: void
	*/
	public function add_share_site($url)
	{
		$this->share_networks_list .= "<li>$url</li>";
	}

	/*
	* returns the list of sharing networks without displaying it
	* @param : void
	* @return: void
	*/
	public function get_list()
	{
		return $this->networks_list(); //return $this->share_networks_list;
	}

	/*
	* displays our list of (social network) sites
	* @params: void
	* @return: void
	*/
	public function render()
	{
		echo "<ul>". $this->share_networks_list. "</ul>";
	}
   
	private function networks_list()
	{
		$this->share_networks();
		return $this->share_networks_list;
	}
}

?>