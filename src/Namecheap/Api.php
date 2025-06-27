<?php

declare(strict_types=1);

namespace Namecheap;

use Namecheap\Exception\AuthenticationException;
use Namecheap\Xml;
use Exception;
use UnexpectedValueException;
/**
 * Namecheap API wrapper
 *
 * @author Saddam Hossain <saddamrhossain@gmail.com>
 * @version 2.0
 */
class Api
{
    public string $endPoint = "https://api.namecheap.com/xml.response";
    public ?string $apiUser = null;
    public ?string $apiKey = null;
    public ?string $userName = null;
    public ?string $clientIp = null;
    public array $curlOptions = [];
    public string $returnType = "xml";

    /**
     * Namecheap API constructor
     *
     * @param string|null $apiUser The API username
     * @param string|null $apiKey The API key
     * @param string|null $userName The username for API calls
     * @param string|null $clientIp The client IP address
     * @param string $returnType Response format: 'xml' or 'json' (default: 'xml')
     * @param array $curlOptions Additional cURL options
     */
    public function __construct(
        ?string $apiUser = null,
        ?string $apiKey = null,
        ?string $userName = null,
        ?string $clientIp = null,
        string $returnType = "xml",
        array $curlOptions = []
    ) {
        $this->apiUser = $apiUser;
        $this->apiKey = $apiKey;
        $this->userName = $userName;
        $this->clientIp = $clientIp;
        $this->returnType = $returnType;
        $this->curlOptions = $curlOptions;
    }

    /**
     * Sets the API endpoint URL
     *
     * @param string $endPoint The API endpoint URL
     * @return self Returns self for method chaining
     */
    public function setEndPoint(string $endPoint): self
    {
        $this->endPoint = $endPoint;
        return $this;
    }

    /**
     * Sets the API user
     *
     * @param string $apiUser The API username
     * @return self Returns self for method chaining
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
     * @return self Returns self for method chaining
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * Sets the username for API calls
     *
     * @param string $userName The username
     * @return self Returns self for method chaining
     */
    public function setUserName(string $userName): self
    {
        $this->userName = $userName;
        return $this;
    }

    /**
     * Sets the client IP address
     *
     * @param string $clientIp The client IP address
     * @return self Returns self for method chaining
     */
    public function setClientIp(string $clientIp): self
    {
        $this->clientIp = $clientIp;
        return $this;
    }

    /**
     * Sets a cURL option
     *
     * @param int $key The cURL option constant
     * @param mixed $value The option value
     * @return self Returns self for method chaining
     */
    public function setCurlOption(int $key, mixed $value): self
    {
        $this->curlOptions[$key] = $value;
        return $this;
    }

    /**
     * Sets the response return type
     *
     * @param string $returnType Response format: 'xml', 'json', or 'array'
     * @return self Returns self for method chaining
     * @throws UnexpectedValueException When invalid return type is provided
     */
    public function setReturnType(string $returnType): self
    {
        if (!in_array($returnType, ["xml", "json", "array"], true)) {
            throw new UnexpectedValueException(
                "Invalid return type. Must be xml, json, or array."
            );
        }
        $this->returnType = $returnType;
        return $this;
    }

    /**
     * Enables sandbox mode for testing
     *
     * @return self Returns self for method chaining
     */
    public function enableSandbox(): self
    {
        $this->setEndPoint("https://api.sandbox.namecheap.com/xml.response");
        return $this;
    }

    /**
     * Disables sandbox mode and switches to production
     *
     * @return self Returns self for method chaining
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
     * @param array $data Additional data parameters
     * @return string|array The API response
     */
    public function get(string $command, array $data = []): string|array
    {
        return $this->request($command, $data, "GET");
    }

    /**
     * API call method for sending requests using POST
     *
     * @param string $command The API command to execute
     * @param array $data Additional data parameters
     * @return string|array The API response
     */
    public function post(string $command, array $data = []): string|array
    {
        return $this->request($command, $data, "POST");
    }

    /**
     * Returns null if empty or is not set
     *
     * @param mixed $value The value to check
     * @return mixed The value or null if empty
     */
    protected function checkEmpty(mixed $value): mixed
    {
        return !empty($value) ? $value : null;
    }

    /**
     * Checks if required fields are present in the data array
     *
     * @param array $dataArray The data array to check
     * @param array $requiredFields The list of required fields
     * @return array An array of missing fields
     * @throws Exception If any required fields are missing
     * @throws AuthenticationException If authentication information is missing
     * @throws UnexpectedValueException If the required fields are not an array
     */
    protected function checkRequiredFields(
        array $dataArray,
        array $requiredFields
    ): array {
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (empty($dataArray[$field])) {
                $missingFields[] = $field;
            }
        }
        return $missingFields;
    }

    /**
     * Sends a request to the Namecheap API
     *
     * @param string $command The API command to execute
     * @param array $data Additional data parameters
     * @param string $type The request method: 'GET' or 'POST'
     * @return string|array The API response in the specified format
     * @throws AuthenticationException If authentication information is missing
     * @throws Exception If an error occurs during the request
     * @throws UnexpectedValueException If the return type is invalid
     */
    protected function request(
        string $command,
        array $data = [],
        string $type = "GET"
    ): string|array {
        if (
            empty($this->apiUser) ||
            empty($this->apiKey) ||
            empty($this->clientIp)
        ) {
            throw new AuthenticationException(
                "Authentication information must be provided."
            );
        }

        $url = $this->endPoint;
        $data["ApiUser"] = $this->apiUser;
        $data["ApiKey"] = $this->apiKey;
        $data["UserName"] = $this->userName;
        $data["ClientIp"] = $this->clientIp;
        $data["Command"] = $command;

        // Remove null entries
        $data = array_filter($data, static fn($val) => $val !== null);

        $defaultCurlOptions = [
            CURLOPT_VERBOSE => false,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => "Namecheap SDK 2.0",
        ];

        $curlOptions = array_replace($defaultCurlOptions, $this->curlOptions);
        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);

        $type = strtoupper($type);
        match ($type) {
            "GET" => ($url .= "?" . http_build_query($data)),
            "POST" => [
                curl_setopt($ch, CURLOPT_POST, true),
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data),
            ],
            default => throw new Exception("Invalid request method: " . $type),
        };

        curl_setopt($ch, CURLOPT_URL, $url);

        $xmlData = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (in_array($httpCode, [401, 403], true)) {
            throw new Exception(
                "No Permission to perform this request",
                $httpCode
            );
        }

        if (!empty($error)) {
            throw new Exception($error);
        }

        if ($xmlData === false) {
            throw new Exception("Failed to execute cURL request");
        }

        return match ($this->returnType) {
            "json" => json_encode(
                Xml::createArray($xmlData),
                JSON_THROW_ON_ERROR
            ),
            "array" => Xml::createArray($xmlData),
            default => $xmlData,
        };
    }
}
