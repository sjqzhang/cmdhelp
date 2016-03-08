<?php
/**
 *  Licensed under the terms of the Apache Software License 2.0:
 *  http://www.apache.org/licenses/LICENSE-2.0
 * @name   Common.inc.php
 * @package  Common
 * @subpackage  Common
 * @author    张军强 <s_jqzhang@163.com>    QQ:546499741
 * @version    Common.inc.php 2012-03-15
 */

// set_time_limit(0);

//ini_set("memory_limit","2048M");




function get_filename($suffix='')
{
    $paths=  pathinfo($_SERVER['PHP_SELF']);
    return $paths['filename'].$suffix;
}

function lock()
{
    $file=  get_filename('.lock');
    if(file_exists($file))
    {
        exit;
    } else
    {
        write($file,'');
    }
}

function is_lock()
{
    $file=  get_filename('.lock');
    if(file_exists($file))
        return true;
    else return false;
}

function unlock()
{
    $file=  get_filename('.lock');
    if(file_exists($file))
        unlink($file);
}

function get_server_url($is_https=false)
{
    $prefix=  $is_https?'https://':"http://";
    $paths=pathinfo($_SERVER['PHP_SELF']);
    return $prefix.$_SERVER['HTTP_HOST'].$paths['dirname'];
}

function out($cxt)
{
    if(is_array($cxt)||is_object($cxt))
    {
        print_r($cxt);
    } else
    {
        echo $cxt;
    }
}

function cur_date($fmt='Y-m-d H:i:s')
{
    return date($fmt);
}

function file_ext($file_name)
{
    $extend = pathinfo($file_name);
    $extend = strtolower($extend["extension"]);
    return $extend;
}

function curl_get($url, $method = "", $post = "")
{
    $ran = rand(1, 255);
    $ran1 = rand(1, 255);
    $ran2 = rand(1, 255);
    $ran3 = rand(60, 255);
    $ran3 = str_replace(array('192', '172', '127'), "", $ran3);
    $ip = "$ran3.$ran2.$ran1.$ran";
    $headerArr = array("CLIENT-IP:$ip", "X-FORWARDED-FOR:$ip");
    //$cookieJar = tempnam(XZ_ROOT . "./cookie", 'cookie.txt');
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt ($curl, CURLOPT_HTTPHEADER , $headerArr);
    curl_setopt ($curl, CURLOPT_REFERER, "http://www.baidu.com/");
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    // curl_setopt($curl,CURLOPT_TIMEOUT,3); //设定最大访问耗时
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    //curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieJar);
    //curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieJar);
    if ($method == 'post') {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,$post);
    }
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $str = curl_exec($curl);
    //$str = iconv("GBK", "UTF-8//IGNORE", $str);
    curl_close($curl);
    unset($curl);
    return $str;
}


function image_resize($src_file, $dst_file , $new_width , $new_height)
{
    if($new_width <1 || $new_height <1) {
        echo "params width or height error !";
        exit();
    }
    if(!file_exists($src_file)) {
        return;
        //  echo $src_file . " is not exists !";
        // exit();
    }
    // 图像类型
    //$type=exif_imagetype($src_file);
    $type= strtolower( file_ext($src_file));
    //$support_type=array(IMAGETYPE_JPEG , IMAGETYPE_PNG , IMAGETYPE_GIF);

    $support_type=array('jpg' , 'png' , 'gif');

    if(!in_array($type, $support_type,true)) {
        // echo "this type of image does not support! only support jpg , gif or png";
        // exit();
        return ;
    }
    //Load image
    switch($type) {
        case 'jpg' :
            $src_img=imagecreatefromjpeg($src_file);
            break;
        case 'png' :
            $src_img=imagecreatefrompng($src_file);
            break;
        case 'gif' :
            $src_img=imagecreatefromgif($src_file);
            break;
        default:
            echo "Load image error!";
            exit();
    }
    $w=imagesx($src_img);
    $h=imagesy($src_img);
    $ratio_w=1.0 * $new_width / $w;
    $ratio_h=1.0 * $new_height / $h;
    $ratio=1.0;
    // 生成的图像的高宽比原来的都小，或都大 ，原则是 取大比例放大，取大比例缩小（缩小的比例就比较小了）
    if( ($ratio_w < 1 && $ratio_h < 1) || ($ratio_w > 1 && $ratio_h > 1)) {
        if($ratio_w < $ratio_h) {
            // $ratio = $ratio_h ; // 情况一，宽度的比例比高度方向的小，按照高度的比例标准来裁剪或放大
            $ratio = $ratio_w;
            $new_height=  (int) ($new_height * $ratio_h);        //重新定义高度
            //echo $ratio_w."<br>".$ratio_h;
        }else {
            $ratio = $ratio_w ;
        }
        // 定义一个中间的临时图像，该图像的宽高比 正好满足目标要求
        $inter_w=(int)($new_width / $ratio);
        $inter_h=(int) ($new_height / $ratio);
        $inter_img=imagecreatetruecolor($inter_w , $inter_h);
        imagecopy($inter_img, $src_img, 0,0,0,0,$inter_w,$inter_h);
        // 生成一个以最大边长度为大小的是目标图像$ratio比例的临时图像
        // 定义一个新的图像
        $new_img=imagecreatetruecolor($new_width,$new_height);
        imagecopyresampled($new_img,$inter_img,0,0,0,0,$new_width,$new_height,$inter_w,$inter_h);
        switch($type) {
            case 'jpg' :
                imagejpeg($new_img, $dst_file,100); // 存储图像
                break;
            case 'png' :
                imagepng($new_img,$dst_file,100);
                break;
            case 'gif' :
                imagegif($new_img,$dst_file,100);
                break;
            default:
                break;
        }
    } // end if 1
    // 2 目标图像 的一个边大于原图，一个边小于原图 ，先放大平普图像，然后裁剪
    // =if( ($ratio_w < 1 && $ratio_h > 1) || ($ratio_w >1 && $ratio_h <1) )
    else{
        $ratio=$ratio_h>$ratio_w? $ratio_h : $ratio_w; //取比例大的那个值
        // 定义一个中间的大图像，该图像的高或宽和目标图像相等，然后对原图放大
        $inter_w=(int)($w * $ratio);
        $inter_h=(int) ($h * $ratio);
        $inter_img=imagecreatetruecolor($inter_w , $inter_h);
        //将原图缩放比例后裁剪
        imagecopyresampled($inter_img,$src_img,0,0,0,0,$inter_w,$inter_h,$w,$h);
        // 定义一个新的图像
        $new_img=imagecreatetruecolor($new_width,$new_height);
        imagecopy($new_img, $inter_img, 0,0,0,0,$new_width,$new_height);
        switch($type) {
            case 'jpg' :
                imagejpeg($new_img, $dst_file,100); // 存储图像
                break;
            case 'png' :
                imagepng($new_img,$dst_file,100);
                break;
            case 'gif' :
                imagegif($new_img,$dst_file,100);
                break;
            default:
                break;
        }
    }// if3
}// end function



//获取文件目录列表,该方法返回数组
function get_dir_list($dir) {
    $dirArray[]=NULL;
    if (false != ($handle = opendir ( $dir ))) {
        $i=0;
        while ( false !== ($file = readdir ( $handle )) ) {
            //去掉"“.”、“..”以及带“.xxx”后缀的文件
            if ($file != "." && $file != ".."&&!strpos($file,".")) {
                $dirArray[$i]=$file;
                $i++;
            }
        }
        //关闭句柄
        closedir ( $handle );
    }
    return $dirArray;
}

//获取文件列表
function get_file_list($dir) {
    $fileArray[]=NULL;
    if (false != ($handle = opendir ( $dir ))) {
        $i=0;
        while ( false !== ($file = readdir ( $handle )) ) {
            //去掉"“.”、“..”以及带“.xxx”后缀的文件
            if ($file != "." && $file != ".."&&strpos($file,".")) {
                $fileArray[$i]="./imageroot/current/".$file;
                if($i==100){
                    break;
                }
                $i++;
            }
        }
        //关闭句柄
        closedir ( $handle );
    }
    return $fileArray;
}


function get_url($url) {
    $url_parsed = parse_url($url);
    $host =isset( $url_parsed['host'])?$url_parsed['host']:0;
    $port = isset( $url_parsed['port'])?$url_parsed['port']:0;
    if ( $port == 0 ) {
        $port = 80;
    }
    $path =isset( $url_parsed['path'])?$url_parsed['path']:'/';
    if (empty($path)) {
        $path = "/";
    }
    $query= isset( $url_parsed['query'])? $url_parsed['query']:'';
    if ( $query != "" ) {
        $path .= "?".$query;
    }
    $out = "GET {$path} HTTP/1.0\r\nHost: {$host}\r\n\r\n";
    $out .= "Accept: * /*\r\n";
    $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out .= "User-Agent: Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; GTB6; CIBA; .NET CLR 4.0.20506)\r\n";
    if ($fp = @fsockopen( $host, $port, $errno, $errstr, 30 )) {
        fwrite($fp,$out);
        $content='';
        while (!feof($fp)) {
            $content.=fgets($fp, 128);
        }
        fclose($fp);
        preg_match("/<html[\s\S]*>[\s\s]+<\/html>/i",$content,$ret);
        if(isset($ret[0]))
        {
            return $ret[0];
        }
        else
        {
            return '';
        }
    } else {
        return false;
    }
}


function get_debug_stack($basePath=BASE_DIR)
{
    $bt = debug_backtrace();
    array_shift($bt);
    $data = '';
    foreach ($bt as $i=>$point)
    {
        $func = isset($point['function'])?$point['function']:'';
        $file = isset($point['file'])?substr($point['file'], strlen($basePath)):'';
        $line = isset($point['line'])?$point['line']:'';
        $args = isset($point['args'])?$point['args']:'';
        $class = isset($point['class'])?$point['class']:'';
        if ($class)
            $data .= "#$i ${class}->${func} at [$file:$line]\n";
        else
            $data .= "#$i $func at [$file:$line]\n";
    }
    return $data;
}


function get($url)
{
    return file_get_contents($url);
}


function write($fn,$content,$mode='w')
{
    $f= fopen($fn,$mode);
    fwrite($f,$content);
    fclose($f);
    return $content;
}

function open($fn)
{
    $f= fopen($fn,'r');
    $content= fread($f,filesize($fn));
    fclose($f);
    return $content;
}



function each_item($regx,$context,$callback,$level=0)
{
    preg_match_all($regx,$context,$result);
    if(function_exists($callback)&&count($result)>0&&isset($result[$level]))
    {
        foreach ($result[$level] as $value) {
            $callback($value);
        }
    }
}

function match_all($regx,$context,$callback="",$level=0)
{
    preg_match_all($regx,$context,$result);
    if(function_exists($callback)&&count($result)>0)
    {
        if($level!=-1&&isset($result[$level]))
        { $callback($result[$level]);} else{ $callback($result);}
    } else if(!function_exists($callback)){
        return isset($result[$level])?$result[$level]:$result;
    }
}


function match_all_rows($regx,$context,$callback="")
{
    $rows= match_all($regx,$context,$callback,-1);
    $clen=count($rows);
    $rlen=count($rows[1]);
    $result=$clen==2?array():array(array());
    for($i=1;$i<$clen;$i++){
        if($clen==2)
        {
            for($j=0;$j<$rlen;$j++){
                $result[$j]= $rows[$i][$j];
            }
        }   else
        {
            for($j=0;$j<$rlen;$j++){
                $result[$j][$i]= trim_html($rows[$i][$j]);
            }
        }
    }
    if(function_exists($callback))
    {
        foreach ($result as $value) {
            $callback($value);
        }
    }
    return $result;
}


function match_table($context,$callback="")
{
    $table=match_all("/<table[^>]*>([\S\s]*?)?<\/table>/i",$context,1);
    $tr=match_all("/<tr[^>]*>([\S\s]*?)?<\/tr>/i",$table[0],1);
    $td=match_all("/<t[dh][^>]*>([\S\s]*?)?<\/t[dh]>/i",$tr[count($tr)-1],1);
    $reg="/<tr[^>]*>[\s\S]*?";
    for($i=0;$i<count($td);$i++){
        $reg.="<t[dh][^>]*>([\S\s]*?)?<\/t[dh]>[\s\S]*?";
    }
    $reg.="<\/tr>/i";
    return match_all_rows($reg,$table[0],$callback);
}

function match_all_table($context,$callback="")
{
    $tables=match_all("/<table[^>]*>([\S\s]*?)?<\/table>/",$context,-1);
    $result=array();
    for($i=0;$i<count($tables);$i++){
        $result[$i]=match_table($tables[$i]);
    }
    return $result;
}

function match_by($type,$name,$context,$level)
{

    if($type=='id')
    {
        $p="/<(\w+)[^>]*id=['\"]".$name."['\"][^>]*>([\s\S]*?)?<\/\\1>/i";
    } else if($type=='class')
    {
        $p="/<(\w+)[^>]*class=['\"]".$name."['\"][^>]*>([\s\S]*?)?<\/\\1>/i";
    }   else if($type=='tag')
    {
        $p="/<(".$name.")[^>]*>([\s\S]*?)?<\/\\1>/i";
        if(!preg_match($p,$context))
        {
            $p="/<(".$name.")[^>]*?\/>/i";
        }
    }
    return match_all($p,$context,'',0);
}

function match_by_id($id,$context)
{
    return match_by('id',$id,$context,0);
}

function match_by_class($class,$context)
{

    return match_by('class',$class,$context,0);
}

function match_by_tag($tag,$context)
{

    return match_by('tag',$tag,$context,0);
}

function trim_html($context)
{
    /*
    $p="/<(\w+)[^>]*>([\s\S]*?)?<\/\\1>/";
  if(preg_match($p,$context))
  {
    $result=match_all($p,$context,"",2);
  } else
  {
      return $context;
  }

  return $result[0];
  */

    return preg_replace("/<[^>]+>/","",$context);
}

function trim_xml($context)
{
    $context= preg_replace("/<[^>]+>/","",$context);
    $context= preg_replace("/\]\]>/","",$context);
    return $context;
}




class DB
{
    var $host='127.0.0.1';
    var $pwd='1016';
    var $db='test';
    var $user='root';
    var $db_type='mysql';
    var $db_port=3306;
    var $con=null;
    function __construct($host,$user,$pwd,$db,$port=3306,$db_type='mysql',$charset='utf8')
    {
        $this->host=$host;
        $this->user=$user;
        $this->pwd=$pwd;
        $this->db=$db;
        if($db_type=='mysql')
        {
            $this->db_port= $port==3306?3306:$port;
        } else  if($db_type=='mssql')
        {
            $this->func_connect='mssql_connect';
            $this->func_query='mssql_query';
            $this->func_close='mssql_close';
            $this->func_escape_string='';
            $this->func_select_db='mssql_select_db';
            $this->func_fetch_array='mssql_fetch_array';
            $this->func_affected_rows='mssql_affected_rows';
            $this->tran_start='begin transaction';
            $this->db_type='mssql';
            $this->db_port= $port==3306?1433:$port;
        } else
        {
            exit("db $db_type not spport!!!");
        }
        $this->connect();
        if($db_type=='mysql')
        {
            $this->query("set names $charset");
        }
    }
    function getResult($rs)
    {
        if(is_resource($rs))
        {
            $rows=array();
            $i=0;
            $func =$this->func_fetch_array;
            while($row= $func($rs))
            {
                $rows[$i]=$row;
                $i=$i+1;
            }
            return $rows;
        } else if(is_bool($rs)){
            return $rs;
        }else
        {
            return null;
        }
    }
    function start_tran()
    {
        $this->query($this->tran_start);
    }
    function commit()
    {
        $this->query($this->tran_commit);
    }
    function rollback()
    {
        $this->query($this->tran_rollback);
    }
    function close()
    {
        $func=$this->func_close;
        $func($this->con);
    }
    function affected_rows()
    {
        $func=$this->func_affected_rows;
        return $func($this->con);
    }
    function scalar($sql)
    {
        $row= $this->query($sql);
        if(isset($row[0][0])) {
            return $row[0][0];
        } else if(isset($row[0]))
        {
            return $row[0];
        }  else
        {
            return $row;
        }

    }
    function query($sql)
    {
        $func=$this->func_query;
        $rs=$func($sql,$this->con);
        $sql= trim($sql);
        $sql=trim($sql);
        //echo $sql;
        if(preg_match("/^select|show/im",$sql))
        {
            return $this->getResult($rs);
        } else
        {
            return $rs;
        }
    }
    function format($sql,$para){
        preg_match_all("/:\w+/",$sql,$pn);
        if(count($pn[0])<1) return $sql;
        $func=$this->func_escape_string;
        foreach ($pn[0] as $name) {
            $val="'".$func($para[substr($name,1,strlen($name))])."'";
            $sql= preg_replace("/".$name."/",$val,$sql);
        }
        return $sql;
    }
    function error()
    {  $func=$this->fnuc_error;
        return $func($this->con);
    }

    function get_con()
    {
        return $this->con;
    }
    private function connect()
    {
        $func=$this->func_connect; $this->con= $this->db_type=='mysql'? $func($this->host.":{$this->db_port}",$this->user,$this->pwd):$func($this->host.",{$this->db_port}",$this->user,$this->pwd);
        if(!$this->con) exit("can't connect db,database's name {$this->db}");
        $func=$this->func_select_db; $func($this->db,$this->con);

    }
    var $func_fetch_array='mysql_fetch_array';
    var $func_query='mysql_query';
    var $func_connect='mysql_connect';
    var $fnuc_error='mysql_error';
    var $func_select_db='mysql_select_db';
    var $func_escape_string='mysql_escape_string';
    var $func_affected_rows='mysql_affected_rows';
    var $func_close='mysql_close';
    var $tran_start='start transaction';
    var $tran_commit='commit';
    var $tran_rollback='rollback';


}


// $db=new DB('127.0.0.1','root','1016','test');

//  $db_local=new DB('172.16.1.25','agent_login','KM234ansSONiPUSf','as_mobile','mssql');
//  $db_remote=new DB('58.63.253.77','meizuaspnet','JMS33quwGfMfPWUd','meizu_db_new','mssql');


//$db=new DB('172.16.3.92','root','meizu.com','cmdhelp','mysql');
//   $db_local=new DB('127.0.0.1','root','1016','test');
//    $db_local=new DB('172.16.10.211','root','meizu.com','meizu_bbs');

$db=new DB(SAE_MYSQL_HOST_M,SAE_MYSQL_USER,SAE_MYSQL_PASS,'app_cmdhelp',SAE_MYSQL_PORT,'mysql');
//mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS);


class CmdHelp{


    var  $db=null;


    function __construct($db){
        $this->db=$db;
    }

    function search($keyword=''){
        if($keyword==''){
            $keyword=$_REQUEST['keyword'];
        }
        $keyword=mysql_real_escape_string($keyword);
        $result='';
        if( preg_match('/^\%/', $keyword)){
            $sql="select * from cmdhelp where cmd like '%$keyword%' or cmdinfo like '%$keyword%' or description like '%$keyword%'";
        } else{
            $sql="select * from cmdhelp where cmd = '$keyword'";
        }
        $rows=$this->db->query($sql);
        foreach($rows as $row){
            $description=trim($row['description']);
            if(empty($description)) {
             $result.=$row['id'].".".$row['cmdinfo']."\t".$row['description']."\n";
            } else {
                $result.=$row['id'].".".$row['cmdinfo']."\t//".$row['description']."\n";
            }
        }
        return $result;

    }

    function group_list(){
        $rows=$this->db->query('select cmd from cmdhelp group by cmd');
        $cmd='';
        foreach($rows as $row){
            $cmd.=$row['cmd']."\n";
        }
        return $cmd;
    }

    function get($id){
        $id=mysql_real_escape_string($id);
        $sql="select * from cmdhelp where id='$id'";
        $rows=$this->db->query($sql);
        if(count($rows)>0){
            return $rows[0]['cmdinfo'];
        }

    }

    function add_file($cmd='',$cmdinfo='',$description=''){
        $cmdinfo=$_REQUEST['cmdinfo'];
        $cmd=$_REQUEST['cmd'];
        $description=$_REQUEST['description'];
        $cmdinfo=mysql_real_escape_string($cmdinfo);
        $description=mysql_real_escape_string($description);
        $cmd=mysql_real_escape_string($cmd);
        $sql="INSERT INTO cmdhelp
	(
	cmd,
	cmdinfo,
	description
	)
	VALUES
	(
	'$cmd',
	'$cmdinfo',
	'$description'
	)";
        if($this->db->query($sql)){
            return "success";
        } else {
            return "fail";
        }
    }


    function delete($id=0){
        $id=$_REQUEST['id'];
        $id=mysql_real_escape_string($id);
        $sql="delete from cmdhelp where id='$id'";
        $rows=$this->db->query($sql);
        if($rows>0){
            return "success";
        } else{
            return "fail";
        }
    }

    function add($cmdinfo='',$action='cmd'){
        
        //var_dump($_REQUEST);die;
        $cmdinfo=$_REQUEST['cmdinfo'];
        $action=$_REQUEST['action'];
        if($cmdinfo==""){
            return "cmdinfo is null";
        }
        $cmds=preg_split('/\s+/',$cmdinfo);
        $cmd=$cmds[0];
        //$cmdinfo=preg_replace("/^\s*${cmd}/",'', $cmdinfo);
        
        $description='';
        
        if(strripos($cmdinfo,'//')>0) {
        
         $description= substr($cmdinfo,strripos($cmdinfo,'//')+2, strlen($cmdinfo));            
         $cmdinfo= substr($cmdinfo,0, strripos($cmdinfo,'//'));
         

        }
      
        $cmd=mysql_real_escape_string($cmd);
        $cmdinfo=mysql_real_escape_string($cmdinfo);
        $description=mysql_real_escape_string($description);

        $sql="INSERT INTO cmdhelp
	(
	cmd,
	cmdinfo,
	description
	)
	VALUES
	(
	'$cmd',
	'$cmdinfo',
	'$description'
	)";
        if($this->db->query($sql)){
            return "success";
        } else {
            return "fail";
        }



    }

    function route(){

       $action= substr($_SERVER['PATH_INFO'],1);
       if($action=='list'){
           echo $this->group_list();
       } else {
           if(method_exists($this,$action)){
            echo $this->$action();
           } else {
               echo "no found";
           }
       }

    }



}


$cmd=new CmdHelp($db);

$cmd->route();








?>

