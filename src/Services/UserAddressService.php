<?php

declare(strict_types = 1);

namespace Namecheap\Services;

use Namecheap\ContactBuilder;
use Namecheap\Enums\ServiceClass;
use Namecheap\Response\NamecheapResponse;
use Namecheap\Response\NamecheapResponseParser;
use Namecheap\Traits\FieldValidationTrait;

/**
 * Namecheap User Address Service
 *
 * @author  Joeseph Chen <joeseph.chens@gmail.com>
 * @version 1.0
 */
final class UserAddressService extends ApiService
{
    use FieldValidationTrait;

    private string $command = 'namecheap.users.address.';

    /**
     * Creates a new address for the user
     *
     * @param array $param Address information array with the following keys:
     *                     Required: addressName, emailAddress, firstName, lastName, address1, city,
     *                     stateProvince, stateProvinceChoice, zip, country, phone
     *                     Optional: defaultYN, jobTitle, organization, address2, phoneExt, fax
     *
     * @return NamecheapResponse
     *
     * @note Phone and Fax should be in format +NNN.NNNNNNNNNN
     * @note Country should be a two-letter country code
     * @note DefaultYN: 1 to set as default address, 0 otherwise
     */
    public function create(array $param): NamecheapResponse
    {
        $addressData = $param;

        if (isset($param['zip'])) $addressData['postalCode'] = $param['zip'];

        if (isset($param['organization'])) $addressData['organizationName'] = $param['organization'];

        $requiredFields = ['addressName', 'firstName', 'lastName', 'address1', 'city', 'stateProvince', 'stateProvinceChoice', 'postalCode', 'country', 'emailAddress', 'phone'];
        $reqFields      = $this->checkRequiredFields($addressData, $requiredFields);

        if (count($reqFields)) {
            $fieldList = implode(', ', $reqFields);

            return NamecheapResponseParser::createErrorResponse(
                $fieldList . ' : these fields are required!',
                'namecheap.users.address.create',
                0.0,
                2010324
            );
        }

        $data = ContactBuilder::for(ServiceClass::USER_ADDRESS)->setContactData($addressData)->build();

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }


    /**
     * Deletes the particular address for the user
     *
     * @param int $addressId The unique AddressID to delete
     *
     * @return NamecheapResponse
     */
    public function delete(int $addressId): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, ['AddressID' => $addressId]);
    }

    /**
     * Gets information for the requested addressID
     *
     * @param int $addressId The unique AddressID
     *
     * @return NamecheapResponse
     */
    public function getInfo(int $addressId): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, ['AddressID' => $addressId]);
    }

    /**
     * Gets a list of addressIDs and addressnames associated with the user account
     *
     * @return NamecheapResponse
     */
    public function getList(): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__);
    }

    /**
     * Sets default address for the user
     *
     * @param int $addressId The unique addressID to set as default
     *
     * @return NamecheapResponse
     */
    public function setDefault(int $addressId): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, ['AddressID' => $addressId]);
    }

    /**
     * Updates the particular address of the user
     *
     * @param array $param Address information array with the following keys:
     *                     Required: addressId, addressName, emailAddress, firstName, lastName, address1,
     *                     city, stateProvince, stateProvinceChoice, zip, country, phone
     *                     Optional: defaultYN, jobTitle, organization, address2, phoneExt, fax
     *
     * @return NamecheapResponse
     * @note Phone and Fax should be in format +NNN.NNNNNNNNNN
     * @note Country should be a two-letter country code
     * @note DefaultYN: 1 to set as default address, 0 otherwise
     */
    public function update(array $param): NamecheapResponse
    {
        $addressData = $param;

        if (isset($param['zip'])) $addressData['postalCode'] = $param['zip'];

        if (isset($param['organization'])) $addressData['organizationName'] = $param['organization'];

        $requiredFields = ['addressId', 'addressName', 'firstName', 'lastName', 'address1', 'city', 'stateProvince', 'stateProvinceChoice', 'postalCode', 'country', 'emailAddress', 'phone'];
        $reqFields      = $this->checkRequiredFields($addressData, $requiredFields);

        if (count($reqFields)) {
            $fieldList = implode(', ', $reqFields);

            return NamecheapResponseParser::createErrorResponse(
                $fieldList . ' : these fields are required!',
                'namecheap.users.address.update',
                0.0,
                2010324
            );
        }

        $data = ContactBuilder::for(ServiceClass::USER_ADDRESS)->setContactData($addressData)->build();

        $data['AddressId'] = $param['addressId'] ?? null;

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }
}