<?php

namespace Modules\AiReception\Agenda\Http;

class Handler extends \Illuminate\Foundation\Exceptions\Handler
{
    public function render($request, \Exception $e)
    {
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return response()->json(['message' => 'Revisa los datos.', 'errors' => $e->errors()], 422);
        }
        if ($e instanceof \InvalidArgumentException) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface ? $e->getStatusCode() : ($e instanceof \Illuminate\Session\TokenMismatchException ? 419 : 500);
        $message = $status === 500 ? 'Error interno del laboratorio.' : ($status === 419 ? 'Sesion vencida. Recarga la pagina.' : ($e->getMessage() ?: 'Operacion no autorizada.'));
        return response()->json(['message' => $message], $status);
    }
}
