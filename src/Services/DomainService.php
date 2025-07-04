<?php

declare(strict_types = 1);

namespace Namecheap\Services;

use Namecheap\ContactBuilder;
use Namecheap\Enums\ServiceClass;
use Namecheap\Response\NamecheapResponse;
use Namecheap\Response\NamecheapResponseParser;
use Namecheap\Traits\FieldValidationTrait;

/**
 * Namecheap Domain Service
 *
 * @author  Joeseph Chen <joeseph.chens@gmail.com>
 * @version 1.0
 */
final class DomainService extends ApiService
{
    use FieldValidationTrait;

    private string $command = "namecheap.domains.";

    /**
     * Returns a list of domains for the particular user
     *
     * @param string|null $searchTerm Keyword to look for in the domain list
     * @param string|null $listType   Possible values are ALL, EXPIRING, or EXPIRED (default: ALL)
     * @param int|null    $page       Page to return (default: 1)
     * @param int|null    $pageSize   Number of domains per page, min 10, max 100 (default: 20)
     * @param string|null $sortBy     Sort order: NAME, NAME_DESC, EXPIREDATE, EXPIREDATE_DESC, CREATEDATE,
     *                                CREATEDATE_DESC
     *
     * @return NamecheapResponse
     */
    public function getList(
        ?string $searchTerm = null,
        ?string $listType = null,
        ?int    $page = null,
        ?int    $pageSize = null,
        ?string $sortBy = null
    ): NamecheapResponse {
        $data = [
            "ListType"   => $listType,
            "SearchTerm" => $searchTerm,
            "Page"       => $page,
            "PageSize"   => $pageSize,
            "SortBy"     => $sortBy,
        ];

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Gets contact information of the requested domain
     *
     * @param string $domainName Domain to get contacts for
     *
     * @return NamecheapResponse
     */
    public function getContacts(string $domainName): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, [
            "DomainName" => $domainName,
        ]);
    }

    /**
     * Registers a new domain name
     *
     * @param array $domainInfo  Domain registration information including:
     *                           - domainName (required): Domain name to register
     *                           - years (required): Number of years to register (default: 2)
     *                           - idnCode (optional): Code of Internationalized Domain Name
     *                           - nameservers (optional): Comma-separated list of custom nameservers
     *                           - addFreeWhoisguard (optional): Adds free WhoisGuard (default: no)
     *                           - wGEnabled (optional): Enables free WhoisGuard (default: no)
     *                           - isPremiumDomain (optional): Indication if domain is premium
     *                           - premiumPrice (optional): Registration price for premium domain
     *                           - eapFee (optional): Purchase fee for premium domain during EAP
     * @param array $contactInfo Contact information for registrant, tech, admin, and auxBilling contacts.
     *                           Each contact type requires: firstName, lastName, address1, city,
     *                           stateProvince, postalCode, country, phone, emailAddress.
     *                           Optional fields: organizationName, jobTitle, address2,
     *                           stateProvinceChoice, phoneExt, fax
     *
     * @return NamecheapResponse The API response containing domain registration result
     */
    public function create(array $domainInfo, array $contactInfo): NamecheapResponse
    {
        $data = $this->parseDomainData($domainInfo, $contactInfo);

        if ($data instanceof NamecheapResponse) return $data;

        return $this->apiClient->post($this->command . __FUNCTION__, $data);
    }

    /**
     * Parse domain data for registration
     *
     * @param array $domainData  Domain data including:
     *                           - domainName (required): Domain name to register
     *                           - years (required): Number of years to register (default: 2)
     *                           - idnCode (optional): Code of Internationalized Domain Name
     *                           - nameservers (optional): Comma-separated list of custom nameservers
     *                           - addFreeWhoisguard (optional): Adds free WhoisGuard (default: no)
     *                           - wGEnabled (optional): Enables free WhoisGuard (default: no)
     *                           - isPremiumDomain (optional): Indication if domain is premium
     *                           - premiumPrice (optional): Registration price for premium domain
     *                           - eapFee (optional): Purchase fee for premium domain during EAP
     * @param array $contactData Contact information for registrant, tech, admin, and auxBilling contacts.
     *                           Each contact type requires: firstName, lastName, address1, city,
     *                           stateProvince, postalCode, country, phone, emailAddress.
     *                           Optional fields: organizationName, jobTitle, address2,
     *                           stateProvinceChoice, phoneExt, fax
     *
     * @return array|NamecheapResponse
     */
    private function parseDomainData(
        array $domainData,
        array $contactData
    ): array|NamecheapResponse {
        $domainInfo = [
            // Required
            "DomainName"    => $domainData["domainName"] ?? null,
            "Years"         => $domainData["years"] ?? null,
            // Optional
            "PromotionCode" => $domainData["promotionCode"] ?? null,
        ];

        $billing = [
            // Optional billing information
            "BillingFirstName"           => $contactData["billingFirstName"] ?? null,
            "BillingLastName"            => $contactData["billingLastName"] ?? null,
            "BillingAddress1"            => $contactData["billingAddress1"] ?? null,
            "BillingAddress2"            => $contactData["billingAddress2"] ?? null,
            "BillingCity"                => $contactData["billingCity"] ?? null,
            "BillingStateProvince"       =>
                $contactData["billingStateProvince"] ?? null,
            "BillingStateProvinceChoice" =>
                $contactData["billingStateProvinceChoice"] ?? null,
            "BillingPostalCode"          => $contactData["billingPostalCode"] ?? null,
            "BillingCountry"             => $contactData["billingCountry"] ?? null,
            "BillingPhone"               => $contactData["billingPhone"] ?? null,
            "BillingPhoneExt"            => $contactData["billingPhoneExt"] ?? null,
            "BillingFax"                 => $contactData["billingFax"] ?? null,
            "BillingEmailAddress"        =>
                $contactData["billingEmailAddress"] ?? null,
        ];

        $extra = [
            // Optional extra settings
            "IdnCode"           => $domainData["idnCode"] ?? null,
            "Nameservers"       => $domainData["nameservers"] ?? null,
            "AddFreeWhoisguard" => $domainData["addFreeWhoisguard"] ?? null,
            "WGEnabled"         => $domainData["wGEnabled"] ?? null,
            "IsPremiumDomain"   => $domainData["isPremiumDomain"] ?? null,
            "PremiumPrice"      => $domainData["premiumPrice"] ?? null,
            "EapFee"            => $domainData["eapFee"] ?? null,
        ];

        $contactInfo = $this->parseContactInfo($contactData);

        if ($contactInfo instanceof NamecheapResponse) return $contactInfo;

        return array_merge(
            $domainInfo,
            $contactInfo,
            $billing,
            $extra
        );
    }

    /**
     * Parse contact information for domain registration
     *
     * @param array $data  Contact information including:
     *                     - registrantFirstName, registrantLastName, registrantAddress1,
     *                     - registrantCity, registrantStateProvince, registrantPostalCode,
     *                     - registrantCountry, registrantPhone, registrantEmailAddress
     *                     - techFirstName, techLastName, techAddress1, techCity,
     *                     - techStateProvince, techPostalCode, techCountry, techPhone,
     *                     - techEmailAddress
     *                     - adminFirstName, adminLastName, adminAddress1, adminCity,
     *                     - adminStateProvince, adminPostalCode, adminCountry, adminPhone,
     *                     - adminEmailAddress
     *                     - auxBillingFirstName, auxBillingLastName, auxBillingAddress1,
     *                     - auxBillingCity, auxBillingStateProvince, auxBillingPostalCode,
     *                     - auxBillingCountry, auxBillingPhone, auxBillingEmailAddress
     *                     - Optional fields for each contact type: organizationName,
     *                     - jobTitle, address2, stateProvinceChoice, phoneExt, fax
     *
     * @return array|NamecheapResponse Parsed contact information ready for API request or error response
     */
    private function parseContactInfo(array $data): array|NamecheapResponse
    {
        $contacts = ContactBuilder::for(ServiceClass::DOMAIN)->setContactData($data)->build();

        $registrantFields = [
            "RegistrantFirstName",
            "RegistrantLastName",
            "RegistrantAddress1",
            "RegistrantCity",
            "RegistrantStateProvince",
            "RegistrantPostalCode",
            "RegistrantCountry",
            "RegistrantPhone",
            "RegistrantEmailAddress"
        ];

        $missingFields = $this->checkRequiredFields($contacts, $registrantFields);
        if (count($missingFields) > 0) {
            $fieldList = implode(", ", $missingFields);

            return NamecheapResponseParser::createErrorResponse(
                $fieldList . " : these fields are required!",
                "domains.create",
                0.0,
                2010324
            );
        }

        return $this->autoFillContactsFromRegistrant($contacts);
    }

    /**
     * Auto-fill missing tech, admin, auxBilling contact info from registrant
     *
     * @param array $contacts Contact array with registrant data
     *
     * @return array
     */
    private function autoFillContactsFromRegistrant(array $contacts): array
    {
        $contactTypes = [
            'Tech'       => 'Tech',
            'Admin'      => 'Admin',
            'AuxBilling' => 'AuxBilling'
        ];

        $contactFields = [
            'FirstName', 'LastName', 'Address1', 'City', 'StateProvince',
            'PostalCode', 'Country', 'Phone', 'EmailAddress'
        ];

        foreach ($contactTypes as $type) {
            foreach ($contactFields as $field) {
                $typeField       = $type . $field;
                $registrantField = 'Registrant' . $field;

                if (empty($contacts[$typeField]) && !empty($contacts[$registrantField])) {
                    $contacts[$typeField] = $contacts[$registrantField];
                }
            }
        }

        return $contacts;
    }


    /**
     * Returns a list of TLDs available in Namecheap
     *
     * @return NamecheapResponse
     */
    public function getTldList(): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__);
    }

    /**
     * Sets contact information for the domain
     *
     * @param array $domainInfo  Domain information including domain name
     * @param array $contactInfo Contact information for all contact types (registrant, tech, admin, auxBilling).
     *                           Each contact type requires: firstName, lastName, address1, city,
     *                           stateProvince, postalCode, country, phone, emailAddress.
     *                           Optional fields: organizationName, jobTitle, address2,
     *                           stateProvinceChoice, phoneExt, fax.
     *                           Missing required tech/admin/auxBilling contacts will be autofilled
     *                           from registrant information.
     *
     * @return NamecheapResponse
     */
    public function setContacts(
        array $domainInfo,
        array $contactInfo
    ): NamecheapResponse {
        $data = $this->parseContactInfo($contactInfo);

        if ($data instanceof NamecheapResponse) return $data;

        return $this->apiClient->post(
            $this->command . __FUNCTION__,
            array_merge($domainInfo, $data)
        );
    }

    /**
     * Checks the availability of domains
     *
     * @param string|array $domain Single domain name or array of domain names to check
     *
     * @return NamecheapResponse
     */
    public function check(string|array $domain): NamecheapResponse
    {
        $data = [];
        if (is_array($domain)) {
            $data["DomainList"] = implode(",", $domain);
        } else {
            $data["DomainList"] = $domain;
        }

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Reactivates an expired domain
     *
     * @param string      $domainName      Domain name to reactivate
     * @param string|null $promotionCode   Promotional (coupon) code for reactivating the domain
     * @param int|null    $yearsToAdd      Number of years after expiry
     * @param bool|null   $isPremiumDomain Indication if the domain name is premium
     * @param float|null  $premiumPrice    Reactivation price for the premium domain
     *
     * @return NamecheapResponse
     */
    public function reactivate(
        string  $domainName,
        ?string $promotionCode = null,
        ?int    $yearsToAdd = null,
        ?bool   $isPremiumDomain = null,
        ?float  $premiumPrice = null
    ): NamecheapResponse {
        $data = [
            "DomainName"      => $domainName,
            "PromotionCode"   => $promotionCode,
            "YearsToAdd"      => $yearsToAdd,
            "IsPremiumDomain" => $isPremiumDomain,
            "PremiumPrice"    => $premiumPrice,
        ];

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Renew a domain
     *
     * @param string      $domainName      Domain name to renew
     * @param int         $years           Number of years to renew
     * @param string|null $promotionCode   Promotional (coupon) code for renewing the domain
     * @param bool|null   $isPremiumDomain Indication if the domain name is premium
     * @param float|null  $premiumPrice    Renewal price for the premium domain
     *
     * @return NamecheapResponse
     */
    public function renew(
        string  $domainName,
        int     $years,
        ?string $promotionCode = null,
        ?bool   $isPremiumDomain = null,
        ?float  $premiumPrice = null
    ): NamecheapResponse {
        $data = [
            "DomainName"      => $domainName,
            "Years"           => $years,
            "PromotionCode"   => $promotionCode,
            "IsPremiumDomain" => $isPremiumDomain,
            "PremiumPrice"    => $premiumPrice,
        ];

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Gets the RegistrarLock status of the requested domain
     *
     * @param string $domainName Domain name to get status for
     *
     * @return NamecheapResponse
     */
    public function getRegistrarLock(string $domainName): NamecheapResponse
    {
        $data = ["DomainName" => $domainName];

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Sets the RegistrarLock status for a domain
     *
     * @param string      $domainName Domain name to set lock status for
     * @param string|null $lockAction Possible values: LOCK, UNLOCK (default: LOCK)
     *
     * @return NamecheapResponse
     */
    public function setRegistrarLock(
        string  $domainName,
        ?string $lockAction = null
    ): NamecheapResponse {
        $data = [
            "DomainName" => $domainName,
            "LockAction" => $lockAction,
        ];

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Returns information about the requested domain
     *
     * @param string      $domainName Domain name for which domain information needs to be requested
     * @param string|null $hostName   Hosted domain name for which domain information needs to be requested
     *
     * @return NamecheapResponse
     */
    public function getInfo(
        string  $domainName,
        ?string $hostName = null
    ): NamecheapResponse {
        $data = [
            "DomainName" => $domainName,
            "HostName"   => $hostName,
        ];

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }

}