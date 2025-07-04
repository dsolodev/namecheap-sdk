Namecheap APIs SDK for PHP 8.4
=======================

[![Latest Version](https://img.shields.io/badge/release-v1.0-blue.svg)]()
[![PHP Version](https://img.shields.io/badge/php-%5E8.4-blue)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

Namecheap APIs SDK for PHP 8.4 is a modern, type-safe package to manage Namecheap APIs. This is a complete refactor
using modern PHP practices and service pattern architecture.

## 🌟 Modern PHP Features

- **🚀 PHP 8.4 Ready**: Built with cutting-edge PHP features and strict typing
- **📦 PSR Standards**: PSR-4 autoloading, PSR-12 coding standards
- **🛡️ Unified Error Handling**: All methods return structured response objects with consistent error handling
- **🔄 Service Pattern**: Dependency injection and separation of concerns
- **⚡ Type Safety**: Full typed properties and return types

## 🎯 Available Services

- **🌐 Domain Management**: Registration, renewal, transfer, contact management
- **🔧 DNS Management**: Record management, email forwarding, nameserver configuration
- **🔒 SSL Certificates**: Purchase, activation, reissue, management
- **👤 User Account**: Balance inquiry, pricing, account management
- **🛡️ WhoisGuard**: Privacy protection management
- **📮 User Address**: Registrant and contact information management

## 📋 Requirements

- PHP 8.4 or higher
- cURL extension
- JSON extension

## 📦 Installation

The recommended way to install Namecheap SDK is through [Composer](http://getcomposer.org):

```bash
composer require dsolodev/namecheap-php-sdk
```

## 🚀 Quick Start

```php
<?php

declare(strict_types=1);

use Namecheap\ApiClient;
use Namecheap\Services\DomainService;
use Namecheap\Services\SslService;
use Namecheap\Services\UserService;
use Namecheap\Response\NamecheapResponse;

// Create API client
$apiClient = new ApiClient(
    apiUser: 'your_api_user',
    apiKey: 'your_api_key', 
    userName: 'your_username',
    clientIp: '192.168.1.100'
);

// Enable sandbox for testing (optional)
$apiClient->enableSandbox();

// Domain management with structured response
$domainService = new DomainService($apiClient);
$response = $domainService->getList();

if ($response->isSuccess()) {
    $domains = $response->getData();
    echo "Found " . count($domains) . " domains\n";
} else {
    foreach ($response->getErrors() as $error) {
        echo "Error: $error\n";
    }
}

// SSL certificate management
$sslService = new SslService($apiClient);
$sslResponse = $sslService->getList();

// Access data easily
$certificates = $sslResponse->getData();
$executionTime = $sslResponse->getExecutionTime();
```

## 🔧 Advanced Configuration

### Custom cURL Options

```php
use Namecheap\ApiClient;

$apiClient = new ApiClient(
    apiUser: 'your_api_user',
    apiKey: 'your_api_key',
    userName: 'your_username', 
    clientIp: '192.168.1.100',
    curlOptions: [
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'MyApp/1.0'
    ]
);
```

### Unified Response Format

All API methods now return a structured `NamecheapApiResponse` object with consistent data access:

```php
$response = $domainService->getList();

// Check success status
if ($response->isSuccess()) {
    // Access data
    $data = $response->getData();
    
    // Get execution time  
    $time = $response->getExecutionTime();
    
    // Access metadata
    $command = $response->getCommand();
} else {
    // Handle errors
    $errors = $response->getErrors();
}

// Convert to different formats if needed
$array = $response->toArray();  // Complete response as array
$json = $response->toJson();    // JSON string
$xml = $response->toXml();      // Original XML
```

## 🛡️ Unified Response Handling

The SDK uses a unified response approach - **all methods return `NamecheapResponse` objects**, never throwing exceptions
for API or validation errors:

```php
use Namecheap\Response\NamecheapResponse;

// All service methods return NamecheapResponse
$response = $domainService->create($domainInfo, $contactInfo);

if ($response->isSuccess()) {
    // Handle successful response
    $data = $response->getData();
    echo "Domain registered successfully!";
} else {
    // Handle errors - no try/catch needed!
    foreach ($response->getErrors() as $error) {
        echo "Error: $error\n";
    }
}

// Check for warnings
if ($response->hasWarnings()) {
    foreach ($response->getWarnings() as $warning) {
        echo "Warning: $warning\n";
    }
}
```

### Consistent Error Handling

No matter what type of error occurs (authentication, validation, API errors), you always get a structured response:

```php
// Authentication errors
$response = $domainService->getList(); // Missing API credentials
if (!$response->isSuccess()) {
    echo $response->getFirstError(); // "[1010101] Authentication information must be provided."
}

// Validation errors  
$response = $domainService->create([], []); // Missing required fields
if (!$response->isSuccess()) {
    echo $response->getFirstError(); // "[2010324] RegistrantFirstName, RegistrantLastName : these fields are required!"
}

// API errors from Namecheap
$response = $domainService->create($validData, $validContacts); // Domain already taken
if (!$response->isSuccess()) {
    echo $response->getFirstError(); // "[2019166] Domain is not available"
}
```

## 📚 Service Documentation

### Domain Service

```php
$domainService = new DomainService($apiClient);

// Get domain list with structured response
$response = $domainService->getList(
    searchTerm: 'example',
    listType: 'ALL',
    page: 1,
    pageSize: 20
);

if ($response->isSuccess()) {
    $domains = $response->getData();
    echo "Execution time: " . $response->getExecutionTime() . "ms\n";
}

// Check domain availability
$availability = $domainService->check(['example.com', 'example.net']);
if ($availability->isSuccess()) {
    $results = $availability->getData();
}

// Register domain
$registration = $domainService->create($domainInfo, $contactInfo);
if ($registration->isSuccess()) {
    echo "Domain registered successfully!\n";
} else {
    foreach ($registration->getErrors() as $error) {
        echo "Registration error: $error\n";
    }
}
```

### DNS Service

```php
use Namecheap\Services\DomainDnsService;

$dnsService = new DomainDnsService($apiClient);

// Get DNS hosts
$hosts = $dnsService->getHosts('example.com');

// Set DNS hosts
$result = $dnsService->setHosts('example.com', $hostRecords);

// Get email forwarding
$forwarding = $dnsService->getEmailForwarding('example.com');
```

### SSL Service

```php
use Namecheap\Services\SslService;

$sslService = new SslService($apiClient);

// Get SSL certificates
$certificates = $sslService->getList();

// Create SSL certificate
$result = $sslService->create($certificateInfo);

// Activate SSL certificate
$result = $sslService->activate($certificateId, $activationInfo);
```

### User Service

```php
use Namecheap\Services\UserService;

$userService = new UserService($apiClient);

// Get account balances
$balances = $userService->getBalances();

// Get pricing information
$pricing = $userService->getPricing();

// Update user information
$result = $userService->update($userInfo);
```

## 🏗️ Architecture

This SDK follows modern PHP practices:

- **Service Pattern**: Each API group has its own service class
- **Dependency Injection**: Services receive ApiClient via constructor
- **Single Responsibility**: Clear separation of concerns
- **Type Safety**: Full type declarations and strict typing
- **Exception Hierarchy**: Specific exceptions for different error types

## 🔄 Migration from v0.x

The new version uses a service pattern instead of inheritance:

```php
// Old way (v0.x)
$api = new Namecheap\Api($credentials);
$domains = $api->domains()->getList();

// New way (v1.0+)
$apiClient = new ApiClient($credentials);
$domainService = new \Namecheap\Services\DomainService($apiClient);
$domains = $domainService->getList();
```

## 🧪 Testing

```php
// Enable sandbox mode for testing
$apiClient->enableSandbox();

// Your test code here...

// Disable sandbox mode
$apiClient->disableSandbox();
```

## 🔗 Useful Links

- [Namecheap API Documentation](https://www.namecheap.com/support/api/)
- [API Sandbox](https://www.sandbox.namecheap.com/)
- [Error Codes Reference](https://www.namecheap.com/support/api/error-codes/)

## 📞 Support

- [GitHub Issues](https://github.com/dsolodev/namecheap-php-sdk/issues)
- [Namecheap API Documentation](https://www.namecheap.com/support/api/)

## 🙏 Acknowledgments

- Original [NaturalBuild/namecheap-sdk](https://github.com/NaturalBuild/namecheap-sdk) for API structure reference

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.