<?php
//获取指定目录下全部文件夹及所有文件
class FileSystem {
    private $z_arr;       //目录原始数据
    private $copy_z_arr;  //目录原始数据及复制数据
    private $search_dir;  //搜索目录
    
    public function __construct($search_dir) {
        $this->search_dir = $search_dir;
    }

    private function get_all_dir($dir,$parent_k='') {
        $file = scandir($dir);
        $file_arr = array();
        foreach ($file as $k => $v) {
            if ($v != '.' && $v != '..') {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $v)) {
                    //文件夹
                    $file_arr['isDir@'.$dir . DIRECTORY_SEPARATOR . $v.'@'.$parent_k.'_'.$k] = $this->get_all_dir($dir . DIRECTORY_SEPARATOR . $v,$parent_k.'_'.$k);
                }else{
                    //文件
                    $file_arr['isFile@'.$dir . DIRECTORY_SEPARATOR . $v.'@'.$parent_k.'_'.$k] = $dir . DIRECTORY_SEPARATOR . $v;
                }
            }
        }
        return $file_arr;
    }
    
    private function zTree_arr($arr,$once = 1) {
        if($once == 1){
            $this->z_arr['isDir@'.$this->search_dir] = array('type' => '1','source_name' => realpath($this->search_dir));
        }
        $once++;
        foreach ($arr as $k => $v) {
            $name = explode('@', $k);
            $real_name = realpath($name[1]);
            if (is_array($v)) {
                $this->z_arr[$k] = array('type' => '1','source_name' => $real_name);
                $this->zTree_arr($v,$once);
            }else {
                $this->z_arr[$k] = array('type' => '2','source_name' => $real_name);
            }
        }

        return $this->z_arr;
    }
    
    public function get_zTree_dir(){
        $file_arr = $this->get_all_dir($this->search_dir);
        return $this->zTree_arr($file_arr);
    }
    
    public function get_copy_to_dir_date($new_dir){
        //$new_dir = realpath($new_dir);
        if(!$this->z_arr){
            $this->get_zTree_dir();
        }
        //return $this->z_arr;
        foreach ($this->z_arr as $k=>$v){
            $str_legth = mb_strlen($this->search_dir);
            $v['destination_name'] = $new_dir.DIRECTORY_SEPARATOR.mb_substr($v['source_name'],$str_legth);
            $this->copy_z_arr[] = $v;
        }
        return $this->copy_z_arr;
    }
    
    public function copy_to_dir($destination_dir){
        if(!$this->copy_z_arr){
            $this->get_copy_to_dir_date($destination_dir);
        }
        
        $recursive=true;
        foreach ($this->copy_z_arr as $k=>$v){
            if($v['type'] == 1){
                if(!file_exists($v['destination_name'])){
                    if(!mkdir($v['destination_name'],0777,$recursive)){
                        return array(false,'程序冒得权限创建目录');
                    };
                }
            }else{
                if(!file_exists($v['destination_name'])){
                    if(!copy($v['source_name'],$v['destination_name'])){
                        //exit('程序冒得权限创建文件');
                        return array(false,'程序冒得权限创建文件');
                    }
                }elseif(file_get_contents($v['destination_name']) != @file_get_contents($v['source_name'])){
                    if(!copy($v['source_name'],$v['destination_name'])){
                        //exit('程序冒得权限创建文件');
                        return array(false,'程序冒得权限创建文件');
                    }
                }
            }
        }
        return array(true,'复制成功');
    }
    
    public function check_dir($destination_dir){
        if(!$this->copy_z_arr){
            $this->get_copy_to_dir_date($destination_dir);
        }

        $no_identical_num = 0;
        $no_identical_arr = array();
        foreach ($this->copy_z_arr as $k=>$v){
            if($v['type'] == 1){
                if(!file_exists($v['destination_name'])){
                    $v['no_identical'] = 1;
                    $no_identical_arr[] = $v;
                    $no_identical_num++;
                }else{
                    $v['no_identical'] = 0;
                }
            }else{
                if(!file_exists($v['destination_name']) || file_get_contents($v['source_name'])!=@file_get_contents($v['destination_name'])){
                    $v['no_identical'] = 1;
                    $no_identical_arr[] = $v;
                    $no_identical_num++;
                }else{
                    $v['no_identical'] = 0;
                }
            }
            $this->copy_z_arr[$k] = $v;
        }
        if($no_identical_num>0){
            return array(false,$no_identical_num,$no_identical_arr,$this->copy_z_arr);
        }else{
            return array(true,$no_identical_num,$no_identical_arr,$this->copy_z_arr);
        }
        
    }
    
    public static function deldir($dir) {
        if(!file_exists($dir)){
            return true;
        }
        //先删除目录下的文件：
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
          if($file!="." && $file!="..") {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                self::deldir($fullpath);
            }
          }
        }
        closedir($dh);
        //删除当前文件夹：
        if(rmdir($dir)) {
          return true;
        } else {
          return false;
        }
    }
    
}

?>