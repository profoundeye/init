<?php
/////////////////////////////////////////////////////////////////
//云边开源轻博, Copyright (C)   2010 - 2011  qing.thinksaas.cn 
//EMAIL:nxfte@qq.com QQ:234027573                              
//$Id: photo.class.php 1158 2012-07-01 14:30:28Z anythink $         


class yb_photo extends basePostModel
{

    function __construct($mconfig){
         parent::__construct($mconfig); 
    }
    
    function add(){
        parent::add();
        $type = explode('|',$this->mconfig['cfg']['imagetype']);
        $ext = '';
        foreach($type as $d){
            $this->imagetype .= '*.'.$d.';';
        }
        $this->imagetype = substr($this->imagetype,0,-1);
        $this->display($this->mconfig['display']);
   }
    
    function saved(){

        $used_image = $this->parseImg($this->spArgs('localimg'));
		//$serial = serialize($used_image);
		//print_r($this->spArgs());
		//print_r($used_image);exit;
        if(is_array($used_image)){
             $bodypre = '[attribute]'.serialize($used_image).'[/attribute]';
        }
      if($_bid = parent::saved($bodypre)){
           header('Location:'.spUrl('product','index',array('bid'=>$_bid)));
       }
	   
	   /*   if($_bid = parent::saved($bodypre)){
			 print_r($_bid);
           header('Location:'.spUrl('main'));
       }*/
    }
    
    function edit(){
        parent::edit();
        $this->attach = spClass('db_attach')->findAll(array('bid'=>$this->spArgs('id')),'','id,path,blogdesc,mime');
		//取出文章中的图片真实顺序
		$rs = spClass('db_blog')->find(array('bid'=>$this->spArgs('id')),"","body");
		
		$body = split_attribute($rs['body']);
		//print_r($this->attach);
		$imgRightArray = $body['attr']['img'];
		foreach($imgRightArray as $i){
			$temp[] = $this->returnTagImg($i['url'],$this->attach);
		}
		//print_r($temp);exit;
		$this->attach = $temp;
        $this->display($this->mconfig['display']);
    }
	
	function returnTagImg($tag,$forest){
		$tag = getBigImg($tag);
		foreach ($forest as $k=>$f) {
			if($f['path']==$tag){
				return $forest[$k];
			}
		}
	}
    
    function uploadimg(){
        parent::swfupload($this->mconfig['cfg']['imagesize'],
                          $this->mconfig['cfg']['imagetype']
        );
    }
    
     /*处理发布图片模型*/
    private function parseImg($image)
    {
	
        if(!is_array($image)){
            exit('File not found');
        }
		$bid  = $this->spArgs('id',0);

        $num = 0;
        $datas = array('count'=>'','img'=>'');
        foreach($image as $id=>$data){
            if($num >= $this->mconfig['cfg']['imagecount']){
                spClass('db_attach')->delBy($id,$this->uid);
                continue;
            }
            
            foreach($data as $url=>$desc){
               $dsc = ($desc == '图片说明（选填）') ? '' : $desc;
				if($bid != 0){
					$url = parent::tmpfile2attach($bid,$url);
					spClass('db_attach')->update(array('id'=>$id,'path'=>getBigImg($url)),array('blogdesc'=>$dsc));
				}else{
					spClass('db_attach')->update(array('id'=>$id),array('blogdesc'=>$dsc));
				}
                $datas['img'][] = array('url'=>$url,'desc'=>$dsc);
			
			
            }

            $num++;
        }
		if($bid != 0){
			spClass('db_attach')->changeId($this->uid,$bid);
		}
        $datas['count'] = count($datas['img']);
        return $datas;
    }
    
    
    function postToConnect($a,$b){
    }
	




}
?>
