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


$y = microtime(true);
//迭代器获取文件列表
$obj=new FilesystemIterator("out");
$files_arr = array();
foreach ($obj as $key => $v) 
{
    $files_arr[]='out/'.$v->getFilename();
}
// p($files_arr);die;


$data = [];
$data['file_name_zip'] = 'zip.zip';//打包文件
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
