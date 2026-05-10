<?php

namespace App\Http\Traits;

use Illuminate\Http\Response;

trait ApiResponser
{
    /**
     * Build valid response
     * @param  string|array $data
     * @param  string $message
     * @param  int $code
     * @return Illuminate\Http\JsonResponse
     */
    public function ResponseJson($data, $message = null, $code = Response::HTTP_OK)
    {
        $response = [
            'data' => $data,
            'success' => true,
            'code' => $code,
            'message' => $message,

        ];

        return response()->json($response, $code);
    }
    /**
     * Build valid response
     * @param  string|array $data
     * @param  string $message
     * @param  int $code
     * @return Illuminate\Http\JsonResponse
     */
    public function SuccessResponse($message = null, $code = Response::HTTP_OK)
    {
        $response = [
            'success' => true,
            'code' => $code,
            'message' => $message,
        ];
        //        return response()->json($response);
        return $response;
    }

    /**
     * Build valid response
     * @param  string|array $data
     * @param  string $message
     * @param  int $code
     * @return Illuminate\Http\JsonResponse
     */
    public function CreatedResponse($data, $message = null, $code = Response::HTTP_OK)
    {
        $response = [
            'data' => $data,
            'success' => true,
            'code' => $code,
            'message' => $message,
        ];
        return response()->json($response, $code);
    }

    /**
     * Build error responses
     * @param  string $message
     * @param  string|array $errorMessages
     * @param  int $code
     * @return Illuminate\Http\JsonResponse
     */
    public function ErrorResponse($message = null, $errorMessages = [], $code = Response::HTTP_PRECONDITION_FAILED)
    {
        $response = [
            'success' => false,
            'code' => $code,
            'message' => $message,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
