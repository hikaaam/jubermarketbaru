<?php

namespace App\Helpers;

class RequestChecker
{
    public static function checkifexist($column, $request_name, $request, $dataTable)
    {
        if ($request->has($request_name)) {
            $databaru = self::add($column, $request_name, $request, $dataTable);
            return $databaru;
        } else {
            return $dataTable;
        }
    }
    public static function add($column, $request_name, $request, $dataTable)
    {
        $dataTable[$column] = $request[$request_name];
        return $dataTable;
    }
    public static function checkArrayifexist($column, $key, $array, $dataTable)
    {
        if (array_key_exists($key, $array)) {
            $databaru = self::add($column, $key, $array, $dataTable);
            return $databaru;
        } else {
            return $dataTable;
        }
    }
}
