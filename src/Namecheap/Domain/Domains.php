<?php

declare(strict_types=1);

namespace Namecheap\Domain;

use Namecheap\Api;
use Exception;
/**
 * Namecheap API wrapper - Domain management
 *
 * @author Saddam Hossain <saddamrhossain@gmail.com>
 * @version 2.0
 */
final class Domains extends Api
{
    protected string $command = "namecheap.domains.";

    /**
     * Returns a list of domains for the particular user
     *
     * @param string|null $listType Possible values are ALL, EXPIRING, or EXPIRED (default: ALL)
     * @param string|null $searchTerm Keyword to look for in the domain list
     * @param int|null $page Page to return (default: 1)
     * @param int|null $pageSize Number of domains per page, min 10, max 100 (default: 20)
     * @param string|null $sortBy Sort order: NAME, NAME_DESC, EXPIREDATE, EXPIREDATE_DESC, CREATEDATE, CREATEDATE_DESC
     */
    public function getList(
        ?string $searchTerm = null,
        ?string $listType = null,
        ?int $page = null,
        ?int $pageSize = null,
        ?string $sortBy = null
    ): string|array {
        $data = [
            "ListType" => $listType,
            "SearchTerm" => $searchTerm,
            "Page" => $page,
            "PageSize" => $pageSize,
            "SortBy" => $sortBy,
        ];
        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Gets contact information of the requested domain
     *
     * @param string $domainName Domain to get contacts for
     */
    public function getContacts(string $domainName): string|array
    {
        return $this->get($this->command . __FUNCTION__, [
            "DomainName" => $domainName,
        ]);
    }

    /**
     * Registers a new domain name
     *
     * @param array $domainInfo Domain registration information including:
     *                         - domainName (required): Domain name to register
     *                         - years (required): Number of years to register (default: 2)
     *                         - idnCode (optional): Code of Internationalized Domain Name
     *                         - nameservers (optional): Comma-separated list of custom nameservers
     *                         - addFreeWhoisguard (optional): Adds free WhoisGuard (default: no)
     *                         - wGEnabled (optional): Enables free WhoisGuard (default: no)
     *                         - isPremiumDomain (optional): Indication if domain is premium
     *                         - premiumPrice (optional): Registration price for premium domain
     *                         - eapFee (optional): Purchase fee for premium domain during EAP
     * @param array $contactInfo Contact information for registrant, tech, admin, and auxBilling contacts.
     *                          Each contact type requires: firstName, lastName, address1, city,
     *                          stateProvince, postalCode, country, phone, emailAddress.
     *                          Optional fields: organizationName, jobTitle, address2,
     *                          stateProvinceChoice, phoneExt, fax
     */
    public function create(array $domainInfo, array $contactInfo): string|array
    {
        $data = $this->parseDomainData($domainInfo, $contactInfo);
        return $this->post($this->command . __FUNCTION__, $data);
    }

    /**
     * Returns a list of TLDs available in Namecheap
     */
    public function getTldList(): string|array
    {
        return $this->get($this->command . __FUNCTION__);
    }

    /**
     * Sets contact information for the domain
     *
     * @param array $domainInfo Domain information including domain name
     * @param array $contactInfo Contact information for all contact types (registrant, tech, admin, auxBilling).
     *                          Each contact type requires: firstName, lastName, address1, city,
     *                          stateProvince, postalCode, country, phone, emailAddress.
     *                          Optional fields: organizationName, jobTitle, address2,
     *                          stateProvinceChoice, phoneExt, fax.
     *                          Missing required tech/admin/auxBilling contacts will be auto-filled
     *                          from registrant information.
     */
    public function setContacts(
        array $domainInfo,
        array $contactInfo
    ): string|array {
        $data = $this->parseContactInfo($contactInfo);
        return $this->post(
            $this->command . __FUNCTION__,
            array_merge($domainInfo, $data)
        );
    }

    /**
     * Checks the availability of domains
     *
     * @param string|array $domain Single domain name or array of domain names to check
     */
    public function check(string|array $domain): string|array
    {
        $data = [];
        if (is_array($domain)) {
            $data["DomainList"] = implode(",", $domain);
        } else {
            $data["DomainList"] = $domain;
        }

        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Reactivates an expired domain
     *
     * @param string $domainName Domain name to reactivate
     * @param string|null $promotionCode Promotional (coupon) code for reactivating the domain
     * @param int|null $yearsToAdd Number of years after expiry
     * @param bool|null $isPremiumDomain Indication if the domain name is premium
     * @param float|null $premiumPrice Reactivation price for the premium domain
     */
    public function reactivate(
        string $domainName,
        ?string $promotionCode = null,
        ?int $yearsToAdd = null,
        ?bool $isPremiumDomain = null,
        ?float $premiumPrice = null
    ): string|array {
        $data = [
            "DomainName" => $domainName,
            "PromotionCode" => $promotionCode,
            "YearsToAdd" => $yearsToAdd,
            "IsPremiumDomain" => $isPremiumDomain,
            "PremiumPrice" => $premiumPrice,
        ];
        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Renew a domain
     *
     * @param string $domainName Domain name to renew
     * @param int $years Number of years to renew
     * @param string|null $promotionCode Promotional (coupon) code for renewing the domain
     * @param bool|null $isPremiumDomain Indication if the domain name is premium
     * @param float|null $premiumPrice Renewal price for the premium domain
     */
    public function renew(
        string $domainName,
        int $years,
        ?string $promotionCode = null,
        ?bool $isPremiumDomain = null,
        ?float $premiumPrice = null
    ): string|array {
        $data = [
            "DomainName" => $domainName,
            "Years" => $years,
            "PromotionCode" => $promotionCode,
            "IsPremiumDomain" => $isPremiumDomain,
            "PremiumPrice" => $premiumPrice,
        ];
        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Gets the RegistrarLock status of the requested domain
     *
     * @param string $domainName Domain name to get status for
     */
    public function getRegistrarLock(string $domainName): string|array
    {
        $data = ["DomainName" => $domainName];
        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Sets the RegistrarLock status for a domain
     *
     * @param string $domainName Domain name to set lock status for
     * @param string|null $lockAction Possible values: LOCK, UNLOCK (default: LOCK)
     */
    public function setRegistrarLock(
        string $domainName,
        ?string $lockAction = null
    ): string|array {
        $data = [
            "DomainName" => $domainName,
            "LockAction" => $lockAction,
        ];
        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Returns information about the requested domain
     *
     * @param string $domainName Domain name for which domain information needs to be requested
     * @param string|null $hostName Hosted domain name for which domain information needs to be requested
     */
    public function getInfo(
        string $domainName,
        ?string $hostName = null
    ): string|array {
        $data = [
            "DomainName" => $domainName,
            "HostName" => $hostName,
        ];
        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Parse domain data for registration
     *
     * @param array $domainData Domain data including:
     *                         - domainName (required): Domain name to register
     *                         - years (required): Number of years to register (default: 2)
     *                         - idnCode (optional): Code of Internationalized Domain Name
     *                         - nameservers (optional): Comma-separated list of custom nameservers
     *                         - addFreeWhoisguard (optional): Adds free WhoisGuard (default: no)
     *                         - wGEnabled (optional): Enables free WhoisGuard (default: no)
     *                         - isPremiumDomain (optional): Indication if domain is premium
     *                         - premiumPrice (optional): Registration price for premium domain
     *                         - eapFee (optional): Purchase fee for premium domain during EAP
     * @param array $contactData Contact information for registrant, tech, admin, and auxBilling contacts.
     *                          Each contact type requires: firstName, lastName, address1, city,
     *                          stateProvince, postalCode, country, phone, emailAddress.
     *                          Optional fields: organizationName, jobTitle, address2,
     *                          stateProvinceChoice, phoneExt, fax
     * @return array Parsed domain data ready for API request
     * @throws Exception If required fields are missing
     */
    private function parseDomainData(
        array $domainData,
        array $contactData
    ): array {
        // Extended attributes : not used
        $domainInfo = [
            // Required
            "DomainName" => $domainData["domainName"] ?? null,
            "Years" => $domainData["years"] ?? null,
            // Optional
            "PromotionCode" => $domainData["promotionCode"] ?? null,
        ];

        $billing = [
            // Optional billing information
            "BillingFirstName" => $contactData["billingFirstName"] ?? null,
            "BillingLastName" => $contactData["billingLastName"] ?? null,
            "BillingAddress1" => $contactData["billingAddress1"] ?? null,
            "BillingAddress2" => $contactData["billingAddress2"] ?? null,
            "BillingCity" => $contactData["billingCity"] ?? null,
            "BillingStateProvince" =>
                $contactData["billingStateProvince"] ?? null,
            "BillingStateProvinceChoice" =>
                $contactData["billingStateProvinceChoice"] ?? null,
            "BillingPostalCode" => $contactData["billingPostalCode"] ?? null,
            "BillingCountry" => $contactData["billingCountry"] ?? null,
            "BillingPhone" => $contactData["billingPhone"] ?? null,
            "BillingPhoneExt" => $contactData["billingPhoneExt"] ?? null,
            "BillingFax" => $contactData["billingFax"] ?? null,
            "BillingEmailAddress" =>
                $contactData["billingEmailAddress"] ?? null,
        ];

        $extra = [
            // Optional extra settings
            "IdnCode" => $domainData["idnCode"] ?? null,
            "Nameservers" => $domainData["nameservers"] ?? null,
            "AddFreeWhoisguard" => $domainData["addFreeWhoisguard"] ?? null,
            "WGEnabled" => $domainData["wGEnabled"] ?? null,
            "IsPremiumDomain" => $domainData["isPremiumDomain"] ?? null,
            "PremiumPrice" => $domainData["premiumPrice"] ?? null,
            "EapFee" => $domainData["eapFee"] ?? null,
        ];

        return array_merge(
            $domainInfo,
            $this->parseContactInfo($contactData),
            $billing,
            $extra
        );
    }

    /**
     * Parse contact information for domain registration
     *
     * @param array $data Contact information including:
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
     * @return array Parsed contact information ready for API request
     * @throws Exception If required fields are missing
     */
    private function parseContactInfo(array $data): array
    {
        $requiredFields = [
            "FirstName",
            "LastName",
            "Address1",
            "City",
            "StateProvince",
            "PostalCode",
            "Country",
            "Phone",
            "EmailAddress",
        ];

        $requiredRegistrant = array_map(
            static fn($f) => "Registrant" . $f,
            $requiredFields
        );
        $requiredTech = array_map(
            static fn($f) => "Tech" . $f,
            $requiredFields
        );
        $requiredAdmin = array_map(
            static fn($f) => "Admin" . $f,
            $requiredFields
        );
        $requiredAuxBilling = array_map(
            static fn($f) => "AuxBilling" . $f,
            $requiredFields
        );

        $registrant = [
            "RegistrantFirstName" => $data["registrantFirstName"] ?? null,
            "RegistrantLastName" => $data["registrantLastName"] ?? null,
            "RegistrantAddress1" => $data["registrantAddress1"] ?? null,
            "RegistrantCity" => $data["registrantCity"] ?? null,
            "RegistrantStateProvince" =>
                $data["registrantStateProvince"] ?? null,
            "RegistrantPostalCode" => $data["registrantPostalCode"] ?? null,
            "RegistrantCountry" => $data["registrantCountry"] ?? null,
            "RegistrantPhone" => $data["registrantPhone"] ?? null,
            "RegistrantEmailAddress" => $data["registrantEmailAddress"] ?? null,
            // Optional
            "RegistrantOrganizationName" =>
                $data["RegistrantOrganizationName"] ?? null,
            "RegistrantJobTitle" => $data["registrantJobTitle"] ?? null,
            "RegistrantAddress2" => $data["registrantAddress2"] ?? null,
            "RegistrantStateProvinceChoice" =>
                $data["registrantStateProvinceChoice"] ?? null,
            "RegistrantPhoneExt" => $data["registrantPhoneExt"] ?? null,
            "RegistrantFax" => $data["registrantFax"] ?? null,
        ];

        $tech = [
            // Required
            "TechFirstName" => $data["techFirstName"] ?? null,
            "TechLastName" => $data["techLastName"] ?? null,
            "TechAddress1" => $data["techAddress1"] ?? null,
            "TechCity" => $data["techCity"] ?? null,
            "TechStateProvince" => $data["techStateProvince"] ?? null,
            "TechPostalCode" => $data["techPostalCode"] ?? null,
            "TechCountry" => $data["techCountry"] ?? null,
            "TechPhone" => $data["techPhone"] ?? null,
            "TechEmailAddress" => $data["techEmailAddress"] ?? null,
            // Optional
            "TechOrganizationName" => $data["techOrganizationName"] ?? null,
            "TechJobTitle" => $data["techJobTitle"] ?? null,
            "TechAddress2" => $data["techAddress2"] ?? null,
            "TechStateProvinceChoice" =>
                $data["techStateProvinceChoice"] ?? null,
            "TechPhoneExt" => $data["techPhoneExt"] ?? null,
            "TechFax" => $data["techFax"] ?? null,
        ];

        $admin = [
            // Required
            "AdminFirstName" => $data["adminFirstName"] ?? null,
            "AdminLastName" => $data["adminLastName"] ?? null,
            "AdminAddress1" => $data["adminAddress1"] ?? null,
            "AdminCity" => $data["adminCity"] ?? null,
            "AdminStateProvince" => $data["adminStateProvince"] ?? null,
            "AdminPostalCode" => $data["adminPostalCode"] ?? null,
            "AdminCountry" => $data["adminCountry"] ?? null,
            "AdminPhone" => $data["adminPhone"] ?? null,
            "AdminEmailAddress" => $data["adminEmailAddress"] ?? null,
            // Optional
            "AdminOrganizationName" => $data["adminOrganizationName"] ?? null,
            "AdminJobTitle" => $data["adminJobTitle"] ?? null,
            "AdminAddress2" => $data["adminAddress2"] ?? null,
            "AdminStateProvinceChoice" =>
                $data["adminStateProvinceChoice"] ?? null,
            "AdminPhoneExt" => $data["adminPhoneExt"] ?? null,
            "AdminFax" => $data["adminFax"] ?? null,
        ];

        $auxBilling = [
            // Required
            "AuxBillingFirstName" => $data["auxBillingFirstName"] ?? null,
            "AuxBillingLastName" => $data["auxBillingLastName"] ?? null,
            "AuxBillingAddress1" => $data["auxBillingAddress1"] ?? null,
            "AuxBillingCity" => $data["auxBillingCity"] ?? null,
            "AuxBillingStateProvince" =>
                $data["auxBillingStateProvince"] ?? null,
            "AuxBillingPostalCode" => $data["auxBillingPostalCode"] ?? null,
            "AuxBillingCountry" => $data["auxBillingCountry"] ?? null,
            "AuxBillingPhone" => $data["auxBillingPhone"] ?? null,
            "AuxBillingEmailAddress" => $data["auxBillingEmailAddress"] ?? null,
            // Optional
            "AuxBillingOrganizationName" =>
                $data["auxBillingOrganizationName"] ?? null,
            "AuxBillingJobTitle" => $data["auxBillingJobTitle"] ?? null,
            "AuxBillingAddress2" => $data["auxBillingAddress2"] ?? null,
            "AuxBillingStateProvinceChoice" =>
                $data["auxBillingStateProvinceChoice"] ?? null,
            "AuxBillingPhoneExt" => $data["auxBillingPhoneExt"] ?? null,
            "AuxBillingFax" => $data["auxBillingFax"] ?? null,
        ];

        // Validation fields
        $missingFields = $this->checkRequiredFields(
            $registrant,
            $requiredRegistrant
        );
        if (count($missingFields) > 0) {
            $fieldList = implode(", ", $missingFields);
            throw new Exception(
                $fieldList . " : these fields are required!",
                2010324
            );
        } else {
            // Validate / replace values with $registrant array for tech, admin, auxBilling
            $missingTech = $this->checkRequiredFields($tech, $requiredTech);
            foreach ($missingTech as $field) {
                $tech[$field] =
                    $registrant["Registrant" . substr($field, strlen("Tech"))];
            }

            $missingAdmin = $this->checkRequiredFields($admin, $requiredAdmin);
            foreach ($missingAdmin as $field) {
                $admin[$field] =
                    $registrant["Registrant" . substr($field, strlen("Admin"))];
            }

            $missingAuxBilling = $this->checkRequiredFields(
                $auxBilling,
                $requiredAuxBilling
            );
            foreach ($missingAuxBilling as $field) {
                $auxBilling[$field] =
                    $registrant[
                        "Registrant" . substr($field, strlen("AuxBilling"))
                    ];
            }
        }

        return array_merge($registrant, $tech, $admin, $auxBilling);
    }
}
