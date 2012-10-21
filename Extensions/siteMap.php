<?php
/**
 * siteMap生成
 */
 /**
  * 
  * 
  */
 class sitemap{
 
 	#RSS还是SITEMAP标识符 取值为 rss,或者 sitemap
 	private $FLAGES = 'rss';

	#SiteMap的默认目录
	private $SITEMAP_PATH = '/';
	
	#组织内容
	private $CONTENT = '';

	#默认权重
	#priority [0.1-1.0]
	private $PRIORITY = '0.1';

	#默认更新频率 
	#changereq ["always", "hourly", "daily", "weekly", "monthly", "yearly"]
	private $CHANGEFREQ = 'weekly';
	
	#地址数据
	# sitemap array(array('loc'=>'','priority'=>'','lastmod'=>'','changefreq'=>''))
	# rss  array()
	private $SITEMAP_DATA = array();
	
	/*
	 * $config = array('flags'		=>'rss',
	 *					'title' 	=>'标题',
	 *					'link'  	=>'链接',
	 *					'desc'		=>'描述',
	 *					'lang'  	=>'zh-CN',
	 *					'pubDate'	=>'发布时间',
	 *					'image'		=>array(
	 *									'link'	=> '封面链接',
	 *									'url' 	=> '封面图片链接',
	 *									'title' => '封面标题',
	 *								),
	 *				);
	 *
	 */
	function __construct($config){
		$this->FLAGES =  $config['flags'];
		if($this->FLAGES == 'sitemap'){
			$this->CONTENT = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		}else if($this->FLAGES == 'rss'){
			$this->CONTENT = '<?xml version="1.0" encoding="UTF-8" ?><rss version="2.0"><channel>';
			$this->CONTENT .= '<title>'.htmlspecialchars($config['title']).'</title>';
			$this->CONTENT .= '<link>'.htmlspecialchars($config['link']).'</link>';
			$this->CONTENT .= '<description>'.htmlspecialchars($config['desc']).'</description>';
			$this->CONTENT .= '<language>'.htmlspecialchars($config['lang']).'</language>';
			$this->CONTENT .= '<pubDate>'.htmlspecialchars($this->dateFormat($config['pubDate'])).'</pubDate>'; 
			$this->CONTENT .= '<image><link>'.htmlspecialchars($config['image']['link']).'</link>';
			$this->CONTENT .= '<url>'.htmlspecialchars($config['image']['url']).'</url>';
			$this->CONTENT .= '<title>'.htmlspecialchars($config['image']['title']).'</title></image>';
		}
	}
	
	/*
	 * sitemap $data = array('loc' => 'http://www.baidu.com',
	 *				'priority' => 0.1,
	 *				'lastmod' => time(),
	 *				'changefreq' => 'aways'
	 *				)
	 *
	 *
	 * rss  $data = array('title' => '标题',
	 *				'link' => '链接',
	 *				'cate' => '分类',
	 *				'desc' => '描述',
	 *				'pubDate' => '更新时间',
	 *				'guid' => '唯一标识符',
	 *				)	  
	 */
	public function add($data){
		if(!is_array($data)){
			return FALSE;
		}
		if($this->FLAGES == 'sitemap'){
			if(!$data['lastmod']){
				$data['lastmod'] = time();
			}	
			if(!$data['priority']){
				$data['priority'] = $this->PRIORITY;
			}
			if(!$data['changefreq']){
				$data['changefreq'] = $this->CHANGEFREQ;
			}
			$data['lastmod'] = $this->dateFormat($data['lastmod']);
			$str = '<url><loc>'.htmlspecialchars($data['loc']).'</loc><priority>'.$data['priority'].'</priority><lastmod>'.$data['lastmod'].'</lastmod><changefreq>'.$data['changefreq'].'</changefreq></url>';
			$this->CONTENT .= $str;
		}else if($this->FLAGES == 'rss'){
			
			$data['pubDate'] = $this->dateFormat($data['pubDate']);
			$this->CONTENT .='<item><title>'.htmlspecialchars($data['title']).'</title><link>'.htmlspecialchars($data['link']).'</link><category>'.htmlspecialchars($data['cate']).'</category><description><![CDATA["'.$data['desc'].'"]]></description><pubDate>'.$data['pubDate'].'</pubDate><guid>'.htmlspecialchars($data['guid']).'</guid></item>';
		}
		return TRUE;
	}
	
	public function addAll($data){
		#不是数组返回 FALSE
		if(!is_array($data)){
			return FALSE;
		}
		
		$ret = array();
		#循环调用添加
		foreach($data as $k=>$v){
			if(FALSE == $this->add($v)){
				$ret['err_count']++;
			}else{
				$ret['suc_count']++;
			}
		}
		return $ret;
	}
	
	public function create(){
		if($this->FLAGES == 'sitemap'){
			$this->CONTENT .= '</urlset>';
		}else if($this->FLAGES == 'rss'){
			$this->CONTENT .= '</channel></rss>';
		}
		return $this->CONTENT;
	}

	public function dateFormat($date){
		if($this->FLAGES == 'sitemap'){
			return date('c',$date);
		}else if($this->FLAGES == 'rss'){
			return date('r',$date);
		}
	}
 }