<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use App\Helper;
class Handler extends ExceptionHandler
{
    public function render($request, \Exception $e)
    {
    if ($e instanceof NotFoundHttpException || $e instanceof MethodNotAllowedHttpException){
        return Helper::notFound('Method Not Found !');
    }
    return parent::render($request, $e);
    }
 
    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'error_flag'    => 1,
            'message' => $this->transformErrors($exception),
            'result'  => NULL,

        ], 200 );
    }

// transform the error messages,
    private function transformErrors(ValidationException $exception)
    {
        $errors = new \stdClass;

        foreach ($exception->errors() as $field => $message) {
           return $message[0];
        }
    }
}
