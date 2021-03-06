<?php namespace Flarum\Api;

use Exception;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Config;

class ExceptionHandler extends Handler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        'Symfony\Component\HttpKernel\Exception\HttpException'
    ];

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($request->is('api/*')) {
            $error = [];
            if (Config::get('app.debug')) {
                $error['code'] = (new \ReflectionClass($e))->getShortName();
            }
            if ($detail = $e->getMessage()) {
                $error['detail'] = $detail;
            }
            $statusCode = $e instanceof HttpException ? $e->getStatusCode() : 500;
            if (count($error)) {
                return $this->renderErrors([$error], $statusCode);
            } else {
                return new Response(null, $statusCode);
            }
        }

        return parent::render($request, $e);
    }

    protected function renderErrors($errors, $httpCode = 500)
    {
        return new JsonResponse(['errors' => $errors], $httpCode);
    }
}
