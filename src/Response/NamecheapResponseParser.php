<?php

declare(strict_types = 1);

namespace Namecheap\Response;

use DOMDocument;
use DOMNode;

final class NamecheapResponseParser
{
    /**
     * Creates a unified response object from Namecheap API XML response
     *
     * @param string $xmlResponse   The raw XML response from Namecheap API
     * @param string $command       The API command that was executed
     * @param float  $executionTime The time taken to execute the command
     * @param array  $meta          Additional metadata to include in the response
     *
     * @return NamecheapResponse
     */
    public static function createFromXml(
        string $xmlResponse,
        string $command,
        float  $executionTime,
        array  $meta = []
    ): NamecheapResponse {
        $startTime  = microtime(true);
        $parsedData = self::parseNamecheapXml($xmlResponse);

        if (isset($parsedData['parseError'])) {
            return self::createErrorResponse(
                $parsedData['message'],
                $command,
                $executionTime,
                0
            );
        }

        $apiResponse        = $parsedData['ApiResponse'] ?? [];
        $status             = $apiResponse['_Status'] ?? 'ERROR';
        $success            = strtoupper($status) === 'OK';
        $errors             = self::extractErrors($apiResponse);
        $warnings           = self::extractWarnings($apiResponse);
        $data               = self::extractResponseData($apiResponse);
        $totalExecutionTime = $executionTime + (microtime(true) - $startTime);
        $responseMeta       = array_merge($meta, [
            'status'              => $status,
            'xmlns'               => $apiResponse['_xmlns'] ?? null,
            'server'              => $apiResponse['_Server'] ?? null,
            'gmt_time_difference' => $apiResponse['_GMTTimeDifference'] ?? null,
        ]);

        return new NamecheapResponse(
            data         : $data,
            success      : $success,
            errors       : $errors,
            warnings     : $warnings,
            command      : $command,
            executionTime: $totalExecutionTime,
            rawXml       : $xmlResponse,
            meta         : array_filter($responseMeta) // Remove null values
        );
    }

    /**
     * Parses the raw XML string from Namecheap API and converts it to an associative array
     *
     * @param string $xmlString
     *
     * @return array
     */
    private static function parseNamecheapXml(string $xmlString): array
    {
        $xml    = new DOMDocument();
        $parsed = $xml->loadXML($xmlString);

        if (!$parsed) return ['parseError' => true, 'message' => "Failed to parse XML response from Namecheap API"];

        $array                                 = [];
        $array[$xml->documentElement->tagName] = self::convertXmlNode($xml->documentElement);

        return $array;
    }

    /**
     * Converts a DOMNode to an associative array
     *
     * @param DOMNode $node
     *
     * @return mixed
     */
    private static function convertXmlNode(DOMNode $node): mixed
    {
        $output = [];

        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;

            case XML_ELEMENT_NODE:
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $value = self::convertXmlNode($child);

                    if (isset($child->tagName)) {
                        $tagName = $child->tagName;

                        if (!isset($output[$tagName])) {
                            $output[$tagName] = [];
                        }

                        $output[$tagName][] = $value;
                    } else {
                        if ($value || $value === '0') {
                            $output = $value;
                        }
                    }
                }

                if (is_array($output)) {
                    foreach ($output as $key => $value) {
                        if (is_array($value) && count($value) === 1) {
                            $output[$key] = $value[0];
                        }
                    }
                }

                if ($node->attributes->length) {
                    $attributes = [];

                    foreach ($node->attributes as $attrName => $attrNode) {
                        $attributes["_$attrName"] = (string)$attrNode->value;
                    }

                    if (!is_array($output)) {
                        $output = ['__text' => $output];
                    } elseif (empty($output)) {
                        $output = [];
                    }

                    $output = array_merge($output, $attributes);
                }
                break;
        }

        return $output;
    }

    /**
     * Creates an error response object for client-side errors
     *
     * @param string $message
     * @param string $command
     * @param float  $executionTime
     * @param int    $errorCode
     *
     * @return NamecheapResponse
     */
    public static function createErrorResponse(
        string $message,
        string $command,
        float  $executionTime,
        int    $errorCode = 0
    ): NamecheapResponse {
        return new NamecheapResponse(
            data         : [],
            success      : false,
            errors       : [$errorCode ? "[$errorCode] $message" : $message],
            warnings     : [],
            command      : $command,
            executionTime: $executionTime,
            rawXml       : '',
            meta         : ['error_type' => 'client_error']
        );
    }

    /**
     * Extracts errors from the API response
     *
     * @param array $apiResponse
     *
     * @return array
     */
    private static function extractErrors(array $apiResponse): array
    {
        $errors = [];

        if (isset($apiResponse['Errors']['Error'])) {
            $errorData = $apiResponse['Errors']['Error'];

            if (isset($errorData['__text'])) {
                $errors[] = self::formatError($errorData);
            } else {
                foreach ($errorData as $error) {
                    if (is_array($error)) {
                        $errors[] = self::formatError($error);
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Formats an error array into a string
     *
     * @param array $error
     *
     * @return string
     */
    private static function formatError(array $error): string
    {
        $errorNumber = $error['_Number'] ?? '';
        $errorText   = $error['__text'] ?? 'Unknown error';

        return $errorNumber ? "[$errorNumber] $errorText" : $errorText;
    }

    private static function extractWarnings(array $apiResponse): array
    {
        $warnings = [];

        if (isset($apiResponse['Warnings']['Warning'])) {
            $warningData = $apiResponse['Warnings']['Warning'];

            if (isset($warningData['__text'])) {
                $warnings[] = $warningData['__text'];
            } else {
                foreach ($warningData as $warning) {
                    if (is_array($warning) && isset($warning['__text'])) {
                        $warnings[] = $warning['__text'];
                    }
                }
            }
        }

        return $warnings;
    }

    /**
     * Extracts the main data from the API response
     *
     * @param array $apiResponse
     *
     * @return array
     */
    private static function extractResponseData(array $apiResponse): array
    {
        $data = [];

        if (isset($apiResponse['CommandResponse'])) {
            $commandResponse = $apiResponse['CommandResponse'];

            foreach ($commandResponse as $key => $value) {
                if (!str_starts_with($key, '_')) {
                    $data[$key] = $value;
                }
            }
        }

        if (isset($apiResponse['RequestedCommand'])) $data['_requestedCommand'] = $apiResponse['RequestedCommand'];

        if (isset($apiResponse['Server'])) $data['_server'] = $apiResponse['Server'];

        return $data;
    }
}