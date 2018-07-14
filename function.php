<?php 
/********************************************************  方法一 ******************************************************/
/**
 * [zip description]
 * @param  [string] $dir_path [要压缩的文件夹名的绝对地址]
 * @param  [string] $zipName  [要压缩的压缩文件名的绝对地址]
 * @return [type]   [description]
 */
function zip($dir_path,$zipName){
    $relationArr = [$dir_path=>[
        'originName'=>$dir_path,
        'is_dir' => true,
        'children'=>[]
    ]];
    modifiyFileName($dir_path,$relationArr[$dir_path]['children']);
    $zip = new ZipArchive();
    $zip->open($zipName,ZipArchive::CREATE);
    zipDir(array_keys($relationArr)[0],'',$zip,array_values($relationArr)[0]['children']);
    $zip->close();
    restoreFileName(array_keys($relationArr)[0],array_values($relationArr)[0]['children']);
}

function zipDir($real_path,$zip_path,&$zip,$relationArr){
    $sub_zip_path = empty($zip_path)?'':$zip_path.'\\';
    if (is_dir($real_path)){
        foreach($relationArr as $k=>$v){
            if($v['is_dir']){  //是文件夹
                $zip->addEmptyDir($sub_zip_path.$v['originName']);
                zipDir($real_path.'\\'.$k,$sub_zip_path.$v['originName'],$zip,$v['children']);
            }else{ //不是文件夹
                $zip->addFile($real_path.'\\'.$k,$sub_zip_path.$k);
                $zip->deleteName($sub_zip_path.$v['originName']);
                $zip->renameName($sub_zip_path.$k,$sub_zip_path.$v['originName']);
            }
        }
    }
}
function modifiyFileName($path,&$relationArr){
    if(!is_dir($path) || !is_array($relationArr)){
        return false;
    }
    if($dh = opendir($path)){
        $count = 0;
        while (($file = readdir($dh)) !== false){
            if(in_array($file,['.','..',null])) continue; //无效文件，重来
            if(is_dir($path.'\\'.$file)){
                $newName = md5(rand(0,99999).rand(0,99999).rand(0,99999).microtime().'dir'.$count);
                $relationArr[$newName] = [
                    'originName' => iconv('GBK','UTF-8',$file),
                    'is_dir' => true,
                    'children' => []
                ];
                rename($path.'\\'.$file, $path.'\\'.$newName);
                modifiyFileName($path.'\\'.$newName,$relationArr[$newName]['children']);
                $count++;
            }
            else{
                $extension = strchr($file,'.');
                $newName = md5(rand(0,99999).rand(0,99999).rand(0,99999).microtime().'file'.$count);
                $relationArr[$newName.$extension] = [
                    'originName' => iconv('GBK','UTF-8',$file),
                    'is_dir' => false,
                    'children' => []
                ];
                rename($path.'\\'.$file, $path.'\\'.$newName.$extension);
                $count++;
            }
        }
    }
}
function restoreFileName($path,$relationArr){
    foreach($relationArr as $k=>$v){
        if(!empty($v['children'])){
            restoreFileName($path.'\\'.$k,$v['children']);
            rename($path.'\\'.$k,iconv('UTF-8','GBK',$path.'\\'.$v['originName']));
        }else{
            rename($path.'\\'.$k,iconv('UTF-8','GBK',$path.'\\'.$v['originName']));
        }
    }
}

### 注意：方法二是自己写的,只能压缩二级目录[结构不乱],多级目录压缩后,目录结构会乱,并且文件中文命名可能会乱码,自己用的时候没有乱码

/********************************************************  方法二 ******************************************************/
/**
 * [zip description]
 * @param  [string] $dir_path 要压缩的文件夹名的绝对地址
 * @param  [string] $zipName  要压缩的压缩文件名的绝对地址
 * @return [type]           [description]
 */
function zip($dir_path, $zipName)
{
    $dir_path = $dir_path.'/';
    static $zip = null;
    if (!$zip) {
        $zip = new ZipArchive();   
        file_exists($zipName) && unlink($zipName);
    }
    if ($zip->open($zipName, ZIPARCHIVE::CREATE)!==TRUE) {   
        exit('无法打开文件，或者文件创建失败');
    }
    $allFile = scandir($dir_path);
    if ($allFile === false) {
        return $zip;
    }
    static $dir = null;
    foreach ($allFile as $k => $file) {
        $tempPath = $dir_path.$file;
        if ($file == '.' || $file == '..'){
            continue;
        } else if (is_dir($tempPath)) {
            $zip->addEmptyDir($file);
            $dir .= $file.'/';
            zip($tempPath,$zipName);
        } else {
            $zip->addFile($tempPath,$dir.$file);
        }
    }
    $zip->close();
    $dir = null;
}
