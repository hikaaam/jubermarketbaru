<?php

namespace App\Helpers;

/**
 * Format response.
 */
class ResponseFormatter
{
    /**
     * API Response
     *
     * @var array
     */
    protected static $response = [
        'code' => 200,
        'success' => true,
        'message' => null,
        'lobj' => [],
    ];

    /**
     * Give success response.
     */
    public static function success($data = [], $message = null)
    {
        self::$response['message'] = $message;
        self::$response['lobj'] = $data;

        return response()->json(self::$response, self::$response['code']);
    }

    /**
     * Give error response.
     */
    public static function error($data = [], $message = null, $code = 200)
    {
        // self::$response['code'] = $code;
        self::$response['success'] = false;
        self::$response['message'] = $message;
        self::$response['lobj'] = $data;

        return response()->json(self::$response, self::$response['code']);
    }
}
