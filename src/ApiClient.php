<?php

declare(strict_types = 1);

namespace Namecheap;

use Namecheap\Response\NamecheapResponse;
use Namecheap\Response\NamecheapResponseParser;

/**
 * Namecheap API HTTP Client
 *
 * @author  Joeseph Chen <joeseph.chens@gmail.com>
 * @version 1.0
 */
class ApiClient
{
    private string  $endPoint    = "https://api.namecheap.com/xml.response";
    private ?string $apiUser     = null;
    private ?string $apiKey      = null;
    private ?string $userName    = null;
    private ?string $clientIp    = null;
    private array   $curlOptions = [];

    /**
     * Namecheap API Client constructor
     *
     * @param string|null $apiUser     The API username
     * @param string|null $apiKey      The API key
     * @param string|null $userName    The username for API calls
     * @param string|null $clientIp    The client IP address
     * @param array       $curlOptions Additional cURL options
     */
    public function __construct(
        ?string $apiUser = null,
        ?string $apiKey = null,
        ?string $userName = null,
        ?string $clientIp = null,
        array   $curlOptions = []
    ) {
        $this->apiUser     = $apiUser;
        $this->apiKey      = $apiKey;
        $this->userName    = $userName;
        $this->clientIp    = $clientIp;
        $this->curlOptions = $curlOptions;
    }

    /**
     * Sets the API user
     *
     * @param string $apiUser The API username
     *
     * @return self
     */
    public function setApiUser(string $apiUser): self
    {
        $this->apiUser = $apiUser;

        return $this;
    }

    /**
     * Sets the API key
     *
     * @param string $apiKey The API key
     *
     * @return self
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Gets the current username
     *
     * @return string|null The current username
     */
    public function getUserName(): ?string
    {
        return $this->userName;
    }

    /**
     * Sets the username for API calls
     *
     * @param string|null $userName The username (null to omit from API calls)
     *
     * @return self
     */
    public function setUserName(?string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Sets the client IP address
     *
     * @param string $clientIp The client IP address
     *
     * @return self
     */
    public function setClientIp(string $clientIp): self
    {
        $this->clientIp = $clientIp;

        return $this;
    }

    /**
     * Sets a cURL option
     *
     * @param int   $key   The cURL option constant
     * @param mixed $value The option value
     *
     * @return self
     */
    public function setCurlOption(int $key, mixed $value): self
    {
        $this->curlOptions[$key] = $value;

        return $this;
    }

    /**
     * Get the current endpoint URL
     *
     * @return string The current API endpoint
     */
    public function getEndPoint(): string
    {
        return $this->endPoint;
    }

    /**
     * Sets the API endpoint URL
     *
     * @param string $endPoint The API endpoint URL
     *
     * @return self
     */
    public function setEndPoint(string $endPoint): self
    {
        $this->endPoint = $endPoint;

        return $this;
    }

    /**
     * Enables sandbox mode for testing
     *
     * @return self
     */
    public function enableSandbox(): self
    {
        $this->setEndPoint("https://api.sandbox.namecheap.com/xml.response");

        return $this;
    }

    /**
     * Disables sandbox mode and switches to production
     *
     * @return self
     */
    public function disableSandbox(): self
    {
        $this->setEndPoint("https://api.namecheap.com/xml.response");

        return $this;
    }

    /**
     * API call method for sending requests using GET
     *
     * @param string $command The API command to execute
     * @param array  $data    Additional data parameters
     *
     * @return NamecheapResponse
     */
    public function get(string $command, array $data = []): NamecheapResponse
    {
        return $this->request($command, $data, "GET");
    }

    /**
     * Sends a request to the Namecheap API
     *
     * @param string $command The API command to execute
     * @param array  $data    Additional data parameters
     * @param string $type    The request method: 'GET' or 'POST'
     *
     * @return NamecheapResponse
     */
    private function request(
        string $command,
        array  $data = [],
        string $type = "GET"
    ): NamecheapResponse {
        $startTime = microtime(true);

        if (
            empty($this->apiUser)
            || empty($this->apiKey)
            || empty($this->clientIp)
        ) {
            $executionTime = (microtime(true) - $startTime) * 1000;

            return NamecheapResponseParser::createErrorResponse(
                "Authentication information must be provided.",
                $command,
                $executionTime,
                1010101
            );
        }

        $url = $this->endPoint;

        $data["ApiUser"]  = $this->apiUser;
        $data["ApiKey"]   = $this->apiKey;
        $data["UserName"] = $this->userName;
        $data["ClientIp"] = $this->clientIp;
        $data["Command"]  = $command;
        $data             = array_filter($data, static fn($val) => $val !== null);

        $defaultCurlOptions = [
            CURLOPT_VERBOSE        => false,
            CURLOPT_FORBID_REUSE   => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => "Namecheap SDK 2.0",
        ];
        $curlOptions        = array_replace($defaultCurlOptions, $this->curlOptions);
        $ch                 = curl_init();

        curl_setopt_array($ch, $curlOptions);

        $type = strtoupper($type);

        switch ($type) {
            case "GET":
                $url .= "?" . http_build_query($data);
                break;
            case "POST":
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                $executionTime = (microtime(true) - $startTime) * 1000;

                return NamecheapResponseParser::createErrorResponse(
                    "Invalid request method: " . $type,
                    $command,
                    $executionTime,
                    0
                );
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        $xmlData  = curl_exec($ch);
        $error    = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $executionTime = (microtime(true) - $startTime) * 1000;

        if (in_array($httpCode, [401, 403], true)) {
            return NamecheapResponseParser::createErrorResponse(
                "No Permission to perform this request",
                $command,
                $executionTime,
                $httpCode
            );
        }

        if (!empty($error)) {
            return NamecheapResponseParser::createErrorResponse(
                $error,
                $command,
                $executionTime,
                0
            );
        }

        if ($xmlData === false) {
            return NamecheapResponseParser::createErrorResponse(
                "Failed to execute cURL request",
                $command,
                $executionTime,
                0
            );
        }

        return NamecheapResponseParser::createFromXml(
            xmlResponse  : $xmlData,
            command      : $command,
            executionTime: $executionTime,
            meta         : [
                'http_code'      => $httpCode,
                'endpoint'       => $this->endPoint,
                'request_method' => $type,
            ]
        );
    }

    /**
     * API call method for sending requests using POST
     *
     * @param string $command The API command to execute
     * @param array  $data    Additional data parameters
     *
     * @return NamecheapResponse
     */
    public function post(string $command, array $data = []): NamecheapResponse
    {
        return $this->request($command, $data, "POST");
    }
}