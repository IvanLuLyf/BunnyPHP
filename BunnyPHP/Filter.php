<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/9/29
 * Time: 13:59
 */

class Filter
{
    const NEXT = 0;
    const STOP = 1;

    public function doFilter()
    {
        return self::NEXT;
    }
}