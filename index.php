<?php

/**
 * [p 打印函数]
 * @Author   W_wang
 * @email    1352255400@qq.com
 * @DateTime 2018-04-20T10:31:12+0800
 * @param    string $str [description]
 * @return   [type]                        [description]
 */
if (!function_exists('p')) {
    function p($str = '')
    {
        echo '<pre>';
        print_r($str);
        echo '</pre>';
    }
}

function calc($size,$digits=2){ 
  $unit= array('','K','M','G','T','P');
  $base= 1024;
  $i = floor(log($size,$base));
  $n = count($unit);
  if($i >= $n){
    $i=$n-1;
  }
  return round($size/pow($base,$i),$digits).' '.$unit[$i] . 'B';
}


/**
 * [filesToZip 文件打包下载]
 * @Author   W_wang
 * @email    1352255400@qq.com
 * @DateTime 2018-07-02T15:07:14+0800
 * @param    array                    $data [文件列表]
 * @return   [type]                         [description]
 */
if (!function_exists('filesToZip')) {
    function filesToZip($data = [])
    {
        //最终生成的文件名（含路径）
        $file_name_zip = isset($data['file_name_zip']) ? $data['file_name_zip'] : time().'.zip';
        $file_list = isset($data['file_list']) ? $data['file_list'] : [];
        if (empty($file_list)) {
            return array('code'=>'1000','data'=>[],'msg'=>'文件列表为空！');
        }

        //检查文件是否生成（存在直接返回）
        if(file_exists($file_name_zip)){
            return array('code'=>'0','data'=>$file_name_zip,'msg'=>'ok');
        }

        //生成文件  
        $zip = new ZipArchive();//使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释  
        //创建压缩包
        if ($zip->open($file_name_zip, ZIPARCHIVE::CREATE)!==TRUE) {
            throw new Exception("无法打开文件，或者文件创建失败");
            return array('code'=>'1000','data'=>[],'msg'=>'无法打开文件，或者文件创建失败');
        }

        //想压缩包中追加文件
        foreach($file_list as $val){
            if(file_exists($val)){
              $re = $zip->addFile( $val, basename($val));//第二个参数是放在压缩包中的文件名称，如果文件可能会有重复，就需要注意一下
            }
        }

        $zip->close();//关闭 

        //检查文件是否生成成功
        if(!file_exists($file_name_zip)){
            throw new Exception("无法找到文件");
            return array('code'=>'1000','data'=>[],'msg'=>'无法找到文件');
        }

        return array('code'=>'0','data'=>$file_name_zip,'msg'=>'ok');
    }
}


ini_set('memory_limit','3072M');    // 临时设置最大内存占用为3G
set_time_limit(0);   // 设置脚本最大执行时间 为0 永不过期

//字段
$fields = 'demo_id,name,age,sex,time_add';
$fields_arr = explode(',', $fields);

//初始化pdo
$pdo = new \PDO("mysql:host=127.0.0.1;dbname=wenhua","root","root",array(\PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8"));

//获取总数
$total = $pdo -> query("SELECT count(1) as total FROM demo limit 1");
$total = $total-> fetch();
$total = isset($total['total']) ? $total['total'] : 0;
//初始化每个文件数量
$file_size = 1000000;
//总文件数
$file_nums = ceil($total/$file_size);

echo "\n";
p('总数：'.$total);

//初始化查询数量（按照字段数增减）
$page_num = 5000;
//初始化总页数（每个文件之间）
$page_num_total = $file_size/$page_num;

$files_arr = array();
$s = microtime(true);
for ($i=1; $i <= $file_nums; $i++) { 
    echo "\n";
	$m_s = memory_get_usage();
	echo  (($i-1)*$file_size).'-'.(($i-1)*$file_size+$file_size)."开始内存: ".calc($m_s)."\n";

	//每个文件开始时间
	$s_p = microtime(true);

	//生成文件并写入表头
	//表头
	$str = "编号,姓名,年龄,性别,时间\n"; 
    $str = iconv('utf-8','gb2312',$str);
    $file_name = 'demo/'.$i.'.csv';
    $files_arr[] = $file_name;
    file_put_contents($file_name, $str);

    //分页查询数据
    for ($ii=1; $ii <= $page_num_total; $ii++) { 
    	//获取内容
    	$limit = ($ii-1)*$page_num+($i-1)*$file_size;//按条件做相应的修改
    	// $limit = $total+11111;
    	$sql = 'SELECT '.$fields.' FROM demo where demo_id > '.$limit.' limit '.$page_num;	  
    	$rs = $pdo -> query($sql);
        $str = '';
		while($row = $rs -> fetch()){					
			foreach ($fields_arr as $k => $v) {
				$val = isset($row[$v]) ? $row[$v] : '';
	            $val = @iconv('utf-8','gb2312//IGNORE',$val); //中文转码
				$str .= $val.',';
			}
            $str .= "\n"; //用引文逗号分开
		}
		//写入内容
    	file_put_contents($file_name, $str, FILE_APPEND);
    }
    echo "\n";
    p((($i-1)*$file_size).'-'.(($i-1)*$file_size+$file_size).'百万耗时：');
    $e = microtime(true) - $s_p;
    p(round($e,3));


    echo "\n";
    $m_s = memory_get_usage();
	echo  (($i-1)*$file_size).'-'.(($i-1)*$file_size+$file_size)."结束内存: ".calc($m_s)."\n";
}
echo "\n";
p('总耗时');
$e = microtime(true) - $s;
p(round($e,3));die;


$y = microtime(true);
$data = [];
$data['file_name_zip'] = 'demo/zip.zip';//打包文件
$data['file_list'] = $files_arr;//文件列表
$data = filesToZip($data);//调用打包函数

echo "\n";
p('压缩耗时');
$e = microtime(true) - $y;
p(round($e,3));die;


if ($data['code'] != 0) {
    exit($data['msg']);
}
$file_name = isset($data['data']) ? $data['data'] : '';
if (empty($file_name)) {
    exit('文件不存在！');
}

//下载文件
// $file_name = APP_PATH.'/import.csv';
header("Content-type:application/octet-stream");
$filename = basename($file_name);
header("Content-Disposition:attachment;filename = ".$filename);
header("Accept-ranges:bytes");
header("Accept-length:".filesize($file_name));
readfile($file_name);
unlink($file_name);//删除文件
foreach ($files_arr as $k => $v) {
	unlink($v);//删除文件
}
die;
