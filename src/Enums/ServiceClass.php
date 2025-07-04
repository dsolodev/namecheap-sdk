<?php

declare(strict_types = 1);

namespace Namecheap\Enums;

/**
 * Service context for different API field mappings
 *
 * @author  Joeseph Chen <joeseph.chens@gmail.com>
 * @version 1.0
 */
enum ServiceClass: string
{
    case DOMAIN       = 'domain';
    case USER         = 'user';
    case USER_ADDRESS = 'user_address';

    /**
     * Get field mappings for the service context
     */
    public function getFieldMappings(): array
    {
        return match ($this) {
            self::DOMAIN       => [
                'firstName'           => 'FirstName',
                'lastName'            => 'LastName',
                'address1'            => 'Address1',
                'address2'            => 'Address2',
                'city'                => 'City',
                'stateProvince'       => 'StateProvince',
                'stateProvinceChoice' => 'StateProvinceChoice',
                'postalCode'          => 'PostalCode',
                'country'             => 'Country',
                'phone'               => 'Phone',
                'phoneExt'            => 'PhoneExt',
                'emailAddress'        => 'EmailAddress',
                'organizationName'    => 'OrganizationName',
                'jobTitle'            => 'JobTitle',
                'fax'                 => 'Fax',
            ],
            self::USER         => [
                'firstName'        => 'FirstName',
                'lastName'         => 'LastName',
                'address1'         => 'Address1',
                'address2'         => 'Address2',
                'city'             => 'City',
                'stateProvince'    => 'StateProvince',
                'postalCode'       => 'Zip',
                'country'          => 'Country',
                'phone'            => 'Phone',
                'phoneExt'         => 'PhoneExt',
                'emailAddress'     => 'EmailAddress',
                'organizationName' => 'Organization',
                'jobTitle'         => 'JobTitle',
                'fax'              => 'Fax',
            ],
            self::USER_ADDRESS => [
                'firstName'           => 'FirstName',
                'lastName'            => 'LastName',
                'address1'            => 'Address1',
                'address2'            => 'Address2',
                'city'                => 'City',
                'stateProvince'       => 'StateProvince',
                'stateProvinceChoice' => 'StateProvinceChoice',
                'postalCode'          => 'Zip',
                'country'             => 'Country',
                'phone'               => 'Phone',
                'phoneExt'            => 'PhoneExt',
                'emailAddress'        => 'EmailAddress',
                'organizationName'    => 'Organization',
                'jobTitle'            => 'JobTitle',
                'fax'                 => 'Fax',
                'addressName'         => 'AddressName',
                'defaultYN'           => 'DefaultYN',
            ],
        };
    }

    /**
     * Get supported contact types for the service context
     */
    public function getSupportedContactTypes(): array
    {
        return match ($this) {
            self::DOMAIN       => ['registrant', 'tech', 'admin', 'auxBilling', 'billing'],
            self::USER,
            self::USER_ADDRESS => [],
        };
    }
}