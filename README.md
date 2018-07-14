# php-zip

使用php自带的ZipArchive类进行文件或文件夹压缩,需要开启php_zip.dll扩展

php_zip.dll扩展下载地址：http://pecl.php.net/package/zip/1.13.5/windows

方法一与方法二的调用方法都是一样的,如下：
	
	//这里写你要压缩的文件夹名的绝对地址
	$dir_path = $_SERVER['DOCUMENT_ROOT'].'/img'; 
	//这里写你要压缩的压缩文件名的绝对地址，不需要创建这个压缩文件，代码里面会新建
	$zipName = $_SERVER['DOCUMENT_ROOT'].'/789.zip';  
	zip($dir_path,$zipName);


	注意：如果在控制器中进行 new \ZipArchive() 与 \ZipArchive::CREATE，需要加上\全局空间，如果在tp框架的公共函数文件中就不用加,，因为没有命名空间
