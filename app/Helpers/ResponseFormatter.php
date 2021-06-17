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
        'data' => [
            'code' => 200,
            'success' => true,
            'message' => null,
            'data' => [],
        ],

    ];

    /**
     * Give success response.
     */
    public static function success($data = [], $message = null)
    {
        self::$response['data']['message'] = $message;
        self::$response['data']['data'] = $data;

        return response()->json(self::$response, self::$response['data']['code']);
    }

    /**
     * Give error response.
     */
    public static function error($data = [], $message = null, $code = 400)
    {
        self::$response['data']['success'] = false;
        self::$response['data']['code'] = $code;
        self::$response['data']['message'] = $message;
        self::$response['data']['data'] = $data;

        return response()->json(self::$response, self::$response['data']['code']);
    }
}
