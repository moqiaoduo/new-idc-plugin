<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (!function_exists('getOption'))
{
    /**
     * 读取option
     *
     * @param $key
     * @param null $default
     * @param int $uid
     * @return mixed|null
     */
    function getOption($key, $default=null, $uid=0) {
        if (Schema::hasTable("options")) {
            $result=DB::table("options")->where("key",$key)->where('uid',$uid)->first();
            if (!$result || $result['value']===null) return $default;
            return $result['value'];
        }
        return $default;
    }
}