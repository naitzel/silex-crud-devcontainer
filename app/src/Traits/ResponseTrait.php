<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Naitzel\SilexCrud\Traits;

trait ResponseTrait
{
    /**
     * Convert some data into a JSON response.
     *
     * @param mixed $data    The response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     *
     * @return JsonResponse
     */
    protected function json($data = array(), $status = 200, array $headers = array())
    {
        return $this->getContainer()->json($data, $status, $headers);
    }

    /**
     * Redireciona pagina.
     *
     * @param       $path
     * @param array $parameters
     *
     * @return mixed
     */
    protected function redirect($path, array $parameters = array())
    {
        return $this->getContainer()->redirect($this->getContainer()['url_generator']->generate($path, $parameters));
    }

    /**
     * Creates a streaming response.
     *
     * @param mixed $callback A valid PHP callback
     * @param int   $status   The response status code
     * @param array $headers  An array of response headers
     *
     * @return StreamedResponse
     */
    protected function stream($callback = null, $status = 200, array $headers = array())
    {
        return $this->getContainer()->stream($callback, $status, $headers);
    }

    /**
     * Aborts the current request by sending a proper HTTP error.
     *
     * @param int    $statusCode The HTTP status code
     * @param string $message    The status message
     * @param array  $headers    An array of HTTP headers
     */
    protected function abort($statusCode, $message = '', array $headers = array())
    {
        return $this->getContainer()->abort($statusCode, $message, $headers);
    }
}
