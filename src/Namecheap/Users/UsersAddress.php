<?php

declare(strict_types=1);

namespace Namecheap\Users;

use Namecheap\Api;
/**
 * Namecheap API wrapper - User address management
 *
 * @author Saddam Hossain <saddamrhossain@gmail.com>
 * @version 2.0
 */
final class UsersAddress extends Api
{


    protected string $command = 'namecheap.users.address.';

    /**
     * Creates a new address for the user
     *
     * @param array $param Address information array with the following keys:
     *   Required: addressName, emailAddress, firstName, lastName, address1, city,
     *             stateProvince, stateProvinceChoice, zip, country, phone
     *   Optional: defaultYN, jobTitle, organization, address2, phoneExt, fax
     *
     * @throws \Exception When required fields are missing
     *
     * @note Phone and Fax should be in format +NNN.NNNNNNNNNN
     * @note Country should be a two-letter country code
     * @note DefaultYN: 1 to set as default address, 0 otherwise
     */
    public function create(array $param): string|array
    {
        $requiredParams = ['AddressName', 'EmailAddress', 'FirstName', 'LastName', 'Address1', 'City', 'StateProvince', 'StateProvinceChoice', 'Zip', 'Country', 'Phone'];

        $data = [
            'AddressName' => $param['addressName'] ?? null,
            'EmailAddress' => $param['emailAddress'] ?? null,
            'FirstName' => $param['firstName'] ?? null,
            'LastName' => $param['lastName'] ?? null,
            'Address1' => $param['address1'] ?? null,
            'City' => $param['city'] ?? null,
            'StateProvince' => $param['stateProvince'] ?? null,
            'StateProvinceChoice' => $param['stateProvinceChoice'] ?? null,
            'Zip' => $param['zip'] ?? null,
            'Country' => $param['country'] ?? null,
            'Phone' => $param['phone'] ?? null,
            'DefaultYN' => $param['defaultYN'] ?? null,
            'JobTitle' => $param['jobTitle'] ?? null,
            'Organization' => $param['organization'] ?? null,
            'Address2' => $param['address2'] ?? null,
            'PhoneExt' => $param['phoneExt'] ?? null,
            'Fax' => $param['fax'] ?? null,
        ];

        $reqFields = $this->checkRequiredFields($data, $requiredParams);
        if (count($reqFields)) {
            $flist = implode(', ', $reqFields);
            throw new \Exception($flist . ' : these fields are required!', 2010324);
        }

        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Deletes the particular address for the user
     *
     * @param int $addressId The unique AddressID to delete
     */
    public function delete(int $addressId): string|array
    {
        return $this->get($this->command . __FUNCTION__, ['AddressID' => $addressId]);
    }

    /**
     * Gets information for the requested addressID
     *
     * @param int $addressId The unique AddressID
     */
    public function getInfo(int $addressId): string|array
    {
        return $this->get($this->command . __FUNCTION__, ['AddressID' => $addressId]);
    }

    /**
     * Gets a list of addressIDs and addressnames associated with the user account
     */
    public function getList(): string|array
    {
        return $this->get($this->command . __FUNCTION__);
    }

    /**
     * Sets default address for the user
     *
     * @param int $addressId The unique addressID to set as default
     */
    public function setDefault(int $addressId): string|array
    {
        return $this->get($this->command . __FUNCTION__, ['AddressID' => $addressId]);
    }

    /**
     * Updates the particular address of the user
     *
     * @param array $param Address information array with the following keys:
     *   Required: addressId, addressName, emailAddress, firstName, lastName, address1,
     *             city, stateProvince, stateProvinceChoice, zip, country, phone
     *   Optional: defaultYN, jobTitle, organization, address2, phoneExt, fax
     *
     * @throws \Exception When required fields are missing
     *
     * @note Phone and Fax should be in format +NNN.NNNNNNNNNN
     * @note Country should be a two-letter country code
     * @note DefaultYN: 1 to set as default address, 0 otherwise
     */
    public function update(array $param): string|array
    {
        $requiredParams = ['AddressId', 'AddressName', 'EmailAddress', 'FirstName', 'LastName', 'Address1', 'City', 'StateProvince', 'StateProvinceChoice', 'Zip', 'Country', 'Phone'];

        $data = [
            'AddressId' => $param['addressId'] ?? null,
            'AddressName' => $param['addressName'] ?? null,
            'EmailAddress' => $param['emailAddress'] ?? null,
            'FirstName' => $param['firstName'] ?? null,
            'LastName' => $param['lastName'] ?? null,
            'Address1' => $param['address1'] ?? null,
            'City' => $param['city'] ?? null,
            'StateProvince' => $param['stateProvince'] ?? null,
            'StateProvinceChoice' => $param['stateProvinceChoice'] ?? null,
            'Zip' => $param['zip'] ?? null,
            'Country' => $param['country'] ?? null,
            'Phone' => $param['phone'] ?? null,
            'DefaultYN' => $param['defaultYN'] ?? null,
            'JobTitle' => $param['jobTitle'] ?? null,
            'Organization' => $param['organization'] ?? null,
            'Address2' => $param['address2'] ?? null,
            'PhoneExt' => $param['phoneExt'] ?? null,
            'Fax' => $param['fax'] ?? null,
        ];

        $reqFields = $this->checkRequiredFields($data, $requiredParams);
        if (count($reqFields)) {
            $flist = implode(', ', $reqFields);
            throw new \Exception($flist . ' : these fields are required!', 2010324);
        }

        return $this->get($this->command . __FUNCTION__, $data);
    }

}