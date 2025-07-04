<?php

declare(strict_types = 1);

namespace Namecheap;

use Namecheap\Enums\ServiceClass;
use Namecheap\Traits\FieldValidationTrait;

/**
 * Contact information builder for Namecheap API
 *
 * @author  Joeseph Chen <joeseph.chens@gmail.com>
 * @version 1.0
 */
class ContactBuilder
{
    use FieldValidationTrait;

    private const array REQUIRED_FIELDS = [
        'firstName', 'lastName', 'address1', 'city', 'stateProvince',
        'postalCode', 'country', 'phone', 'emailAddress'
    ];

    private const array OPTIONAL_FIELDS = [
        'organizationName', 'jobTitle', 'address2', 'stateProvinceChoice',
        'phoneExt', 'fax'
    ];

    private ServiceClass $context;
    private array        $contactData  = [];
    private array        $contactTypes = [];

    /**
     * Create a new ContactBuilder instance with service context
     */
    public static function for(ServiceClass $context): self
    {
        $builder          = new self();
        $builder->context = $context;

        return $builder;
    }

    /**
     * Set contact data for specific contact type
     */
    public function setContact(string $type, array $data): self
    {
        $this->contactTypes[$type] = $data;

        return $this;
    }

    /**
     * Set single contact data (for user/address contexts)
     */
    public function setContactData(array $data): self
    {
        $this->contactData = $data;

        return $this;
    }

    /**
     * Build contact information based on context
     */
    public function build(): array
    {
        return match ($this->context) {
            ServiceClass::DOMAIN       => $this->buildContactsFromPrefixedData(),
            ServiceClass::USER         => $this->buildUserContacts(),
            ServiceClass::USER_ADDRESS => $this->buildUserAddressContacts(),
        };
    }


    /**
     * Map contact fields to API format with optional prefix
     * 
     * Transforms normalized field names to API field names and applies
     * an optional prefix (e.g., 'Registrant', 'Tech', 'Admin').
     */
    private function mapFieldsToApiFormat(array $data, string $prefix = ''): array
    {
        $mapped         = [];
        $fieldMappings  = $this->context->getFieldMappings();
        $normalizedData = $this->normalizeInputData($data);

        foreach ($fieldMappings as $inputField => $apiField) {
            if (isset($normalizedData[$inputField])) {
                $finalApiField          = $prefix . $apiField;
                $mapped[$finalApiField] = $normalizedData[$inputField];
            }
        }

        return $mapped;
    }

    /**
     * Normalize input data to handle field name variations
     */
    private function normalizeInputData(array $data): array
    {
        $normalized = $data;

        if (isset($data['zip']) && !isset($data['postalCode'])) $normalized['postalCode'] = $data['zip'];

        if (isset($data['organization']) && !isset($data['organizationName'])) $normalized['organizationName'] = $data['organization'];

        return $normalized;
    }

    /**
     * Build contact information from prefixed data
     * 
     * Processes contact data with prefixed field names like 'registrantFirstName',
     * 'techLastName', etc. and converts them to API-ready format.
     */
    private function buildContactsFromPrefixedData(): array
    {
        $contacts     = [];
        $contactTypes = ['registrant' => 'Registrant', 'tech' => 'Tech', 'admin' => 'Admin', 'auxBilling' => 'AuxBilling', 'billing' => 'Billing'];

        foreach ($contactTypes as $type => $prefix) {
            $typeData = $this->extractContactDataByPrefix($this->contactData, $type);

            if (!empty($typeData)) {
                $contacts += $this->mapFieldsToApiFormat($typeData, $prefix);
            }
        }

        return $contacts;
    }

    /**
     * Extract contact data for a specific contact type from prefixed field names
     * 
     * Takes prefixed data like 'registrantFirstName', 'registrantLastName' and
     * extracts fields for the specified contact type into a clean array.
     */
    private function extractContactDataByPrefix(array $data, string $type): array
    {
        $extracted = [];
        $allFields = $this->getAllFields();

        foreach ($allFields as $field) {
            $prefixedKey = $type . ucfirst($field);
            if (isset($data[$prefixedKey])) {
                $extracted[$field] = $data[$prefixedKey];
            }
        }

        return $extracted;
    }

    /**
     * Get all possible fields (required + optional)
     */
    public function getAllFields(): array
    {
        return array_merge(self::REQUIRED_FIELDS, self::OPTIONAL_FIELDS);
    }

    /**
     * Build user contact information
     */
    private function buildUserContacts(): array
    {
        return $this->mapFieldsToApiFormat($this->contactData);
    }

    /**
     * Build user address information
     */
    private function buildUserAddressContacts(): array
    {
        return $this->mapFieldsToApiFormat($this->contactData);
    }

    /**
     * Get required fields for contact validation
     */
    public function getRequiredFields(): array
    {
        return self::REQUIRED_FIELDS;
    }
}