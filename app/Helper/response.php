<?php 
namespace App\Helper;
use Illuminate\Http\Response;

class ResponseHelper
{
    public static function success($data = null, $message = 'Success', $code = Response::HTTP_OK)
    {
        return response()->json([
            "success" => true,
            'status' => 200,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function error($message = 'Error', $code = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        return response()->json([
            "success" => false,
            'status' => 'error',
            'message' => $message,
        ], $code);
    }
}
