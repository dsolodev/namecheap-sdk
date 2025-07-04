<?php

declare(strict_types = 1);

namespace Namecheap\Services;

use Namecheap\ApiClient;

/**
 * Abstract API class for all Namecheap services
 *
 * @author  Joeseph Chen <joeseph.chens@gmail.com>
 * @version 1.0
 */
abstract class ApiService
{
    protected readonly ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }
}