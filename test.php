<?php
/**
 * Created by PhpStorm.
 * User: zhiweipang
 * Date: 2018/3/5
 * Time: 下午12:47
 */

//echo json_encode("汉字", JSON_UNESCAPED_UNICODE);
echo unicode_encode("球");


function unicode_encode($name)
{
    $name = iconv('UTF-8', 'UCS-2', $name);
    $len = strlen($name);
    $str = '';
    for ($i = 0; $i < $len - 1; $i = $i + 2)
    {
        $c = $name[$i];
        $c2 = $name[$i + 1];
        if (ord($c) > 0)
        {    // 两个字节的文字
            $s1 = base_convert(ord($c), 10, 16);
            $s2 = base_convert(ord($c2), 10, 16);

            if(ord($c) < 16){
                $s1 = "0".$s1;
            }
            if(ord($c2) < 16){
                $s2 = "0".$s2;
            }
            $str .= $s1 . $s2;
        }
        else
        {
            $str .= $c2;
        }

    }
    return $str;
}