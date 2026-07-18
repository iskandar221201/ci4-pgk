<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseApiController extends BaseController
{
    protected ?object $apiUser = null;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        $this->response->setHeader('Content-Type', 'application/json');

        if (function_exists('auth')) {
            // Check if tokens auth is active
            $authenticator = auth('tokens');
            if ($authenticator->loggedIn()) {
                $this->apiUser = $authenticator->user();
            }
        }

        $method = $this->request->getMethod();
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $contentType = $this->request->getHeaderLine('Content-Type');
            if (!str_contains($contentType, 'application/json')) {
                // Output JSON error dan exit
                $this->response
                    ->setStatusCode(400)
                    ->setJSON(['status' => false, 'code' => 400, 'message' => 'Request body must be application/json', 'errors' => null])
                    ->send();
                exit();
            }
        }
    }
}
