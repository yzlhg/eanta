<?php
    class common{
        
        /**
         * @access public 
         * @param string $dir_path 文件夹
         * @return array 二维数组
         */

        public static function get_dirs($dir_path) {
            $res = array();
            $res_lists = array();
        
            foreach(glob("$dir_path/*") as $item) {
                if(is_dir($item)) {
                    $folder = end(explode('/', $item));
                    $res[$folder] =get_dirs($item);
                    continue;
                }
                $res[] = basename($item);
            }    
            return $res;   
        }



    }

?>