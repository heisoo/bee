<?php
function compress_img ($source) {
    $exts = array("png","bmp","gif","pnm","tiff");
    $start_time = strtotime("-30 day");
    exec("s3cmd ls s3://fever38-us-static/hotdeals/{$source}/ > ./tmp.txt");
    $rs = file('./tmp.txt');

    foreach($rs as $line) {
        $r =  array_filter(explode(' ', $line));
        if(!empty($r[0])){
            $r[0] = trim($r[0]);
            $time = strtotime($r[0]);
        }
        if(!empty($time) && $time >= $start_time){
            if(!empty($r[10])){
                $img = trim($r[10]);
                $path_info = pathinfo($r[10]);
                $ext = trim($path_info["extension"]);
                $file_name = strtolower(trim($path_info["basename"]));

                exec("s3cmd get ".$img);
                exec("cp {$file_name} /mnt/heisoo/s3/{$source}/");

                if (in_array($ext,$exts)) {
                    system("/usr/bin/optipng -o5 ".$file_name);
                }
                if ($ext == "jpg" || $ext == "jpeg") {
                    system("/usr/bin/jpegoptim -o --strip-all ".$file_name);
                }
                system("s3cmd put {$file_name} {$img} --guess-mime-type --add-header 'Cache-Control:max-age=31536000' --add-header 'Expires: Thu, 01 Dec 2014 16:00:00 GMT' --acl-public");
                unlink($file_name);
            }
        }
    }

    unlink('./tmp.txt');
}

compress_img("promotion_main_pic");
compress_img("src_thumb");
//compress_img("uploadImage");
compress_img("dialog_image");
compress_img("joinPicture");
