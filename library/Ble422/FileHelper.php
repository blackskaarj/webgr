<?php
class Ble422_FileHelper {
    /**
     *
     * @param $dirname
     * @return unknown_type
     * credits:
     * http://www.ozzu.com/programming-forum/php-delete-directory-folder-t47492.html
     * July 5th, 2005, 5:44 am
     * placid psychosis
     * # Joined: Jun 08, 2005
        # Posts: 284
        # Loc: Warsaw, IN
     */
    public static function delete_directory($dirname)
    {
        if (is_dir($dirname))
        $dir_handle = opendir($dirname);
        if (!$dir_handle)
        return false;
        while($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname."/".$file)) {
                    unlink($dirname."/".$file);
                } else {
                    //recursive function call
                    $this->delete_directory($dirname.'/'.$file);
                }
            }
        }
        closedir($dir_handle);
        rmdir($dirname);
        return true;
    }
}