<?php
header("Cache-Control: no-cache");
header("Content-type: text/html; charset=utf-8");

//访问例子
ini_set("max_execution_time", 10000);
include_once './FileSystem.php';  



$do       = 'check';
$source_dir = @$_GET['source_dir'];
$destination = @$_GET['destination_dir'];
if(!isset($destination)){
?>
	<form name="myFORM" id="myFORM" action="" method="get">
            对比源文件：&nbsp;
            <select  name="source_dir" id="search_type">
				<option value="G:\pk\branches\pkfast\web">fast_web</option>
				<option value="G:\pk\trunk\web">trunk_web</option>
				<option value="G:\pk\tags\testing\web2.1">testing_web2.1</option>
				<option value="G:\pk\tags\production\web2.1">production_web2.1</option>
            </select>
			对比目标文件夹：
			<select onchange="" name="destination_dir" id="search_type">
				<option value="G:\pk\branches\pkfast\web">fast_web</option>
				<option value="G:\pk\trunk\web">trunk_web</option>
				<option value="G:\pk\tags\testing\web2.1">testing_web2.1</option>
				<option value="G:\pk\tags\production\web2.1">production_web2.1</option>
            </select>
			<input type="submit">
        </form>
<?php
}else{
	
	$msg = 'token验证失败';
	$response = array('code'=>403,'msg'=>$msg,'data'=>null);


	$file_tree_obj = new FileSystem($source_dir);

	list($status,$no_identical_num,$no_identical_arr,$file_tree) = $file_tree_obj->check_dir($destination);

	$c = count($file_tree);
	if($status){
		$response['code'] = '200';
		$response['msg']  = '文件或目录共计'.$c.'个，完全一致.';  
		$response['data']  = $no_identical_arr;
	}else{
		$response['code'] = '500';
		$response['msg']  = '文件或目录共计'.$c.'个，不一致有'.$no_identical_num.'个.';  
		$response['data']  = $no_identical_arr;
	}
	exit(json_encode($response));

}

?>