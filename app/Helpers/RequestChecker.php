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
}
