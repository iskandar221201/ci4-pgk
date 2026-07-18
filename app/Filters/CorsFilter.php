<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $allowedOrigins = env('CORS_ALLOWED_ORIGINS', '*');
        $allowedMethods = env('CORS_ALLOWED_METHODS', 'GET,POST,PUT,PATCH,DELETE,OPTIONS');
        $allowedHeaders = env('CORS_ALLOWED_HEADERS', 'Content-Type,Authorization,X-Requested-With');

        $response = service('response');
        $response->setHeader('Access-Control-Allow-Origin', $allowedOrigins);
        $response->setHeader('Access-Control-Allow-Methods', $allowedMethods);
        $response->setHeader('Access-Control-Allow-Headers', $allowedHeaders);

        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            return $response->setStatusCode(204)->setBody('');
        }
        
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $allowedOrigins = env('CORS_ALLOWED_ORIGINS', '*');
        $allowedMethods = env('CORS_ALLOWED_METHODS', 'GET,POST,PUT,PATCH,DELETE,OPTIONS');
        $allowedHeaders = env('CORS_ALLOWED_HEADERS', 'Content-Type,Authorization,X-Requested-With');

        $response->setHeader('Access-Control-Allow-Origin', $allowedOrigins);
        $response->setHeader('Access-Control-Allow-Methods', $allowedMethods);
        $response->setHeader('Access-Control-Allow-Headers', $allowedHeaders);

        return $response;
    }
}
