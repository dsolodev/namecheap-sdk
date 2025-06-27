<?php

declare(strict_types=1);

namespace Namecheap\Ssl;

use Namecheap\Api;
/**
 * Namecheap API wrapper - SSL certificate management
 *
 * @author Saddam Hossain <saddamrhossain@gmail.com>
 * @version 2.0
 */
final class Ssl extends Api
{
    protected string $command = "namecheap.ssl.";

    /**
     * Creates a new SSL certificate
     *
     * @param int $years Number of years SSL will be issued for (1-2)
     * @param string $type SSL product name (see supported types below)
     * @param int|null $sansToAdd Number of add-on domains for multi-domain certificates
     * @param string|null $promotionCode Promotional (coupon) code for the certificate
     *
     * Supported SSL types: PositiveSSL, EssentialSSL, InstantSSL, InstantSSL Pro,
     * PremiumSSL, EV SSL, PositiveSSL Wildcard, EssentialSSL Wildcard,
     * PremiumSSL Wildcard, PositiveSSL Multi Domain, Multi Domain SSL,
     * Unified Communications, EV Multi Domain SSL
     */
    public function create(
        int $years,
        string $type,
        ?int $sansToAdd = null,
        ?string $promotionCode = null
    ): string|array {
        $data = [
            "Years" => $years,
            "Type" => $type,
            "SANStoADD" => $sansToAdd,
            "PromotionCode" => $promotionCode,
        ];
        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Returns a list of SSL certificates for the user
     *
     * @param string|null $listType Filter type: ALL, Processing, EmailSent, TechnicalProblem,
     *                             InProgress, Completed, Deactivated, Active, Cancelled,
     *                             NewPurchase, NewRenewal (default: ALL)
     * @param string|null $searchTerm Keyword to search for in SSL list
     * @param int|null $page Page to return (default: 1)
     * @param int|null $pageSize Certificates per page, min 10, max 100 (default: 20)
     * @param string|null $sortBy Sort order: PURCHASEDATE, PURCHASEDATE_DESC, SSLTYPE,
     *                           SSLTYPE_DESC, EXPIREDATETIME, EXPIREDATETIME_DESC,
     *                           Host_Name, Host_Name_DESC
     */
    public function getList(
        ?string $listType = null,
        ?string $searchTerm = null,
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
     * Parses the Certificate Signing Request (CSR)
     *
     * @param string $csr Certificate Signing Request
     * @param string|null $certificateType Type of SSL certificate
     *
     * Supported certificate types: InstantSSL, PositiveSSL, PositiveSSL Wildcard,
     * EssentialSSL, EssentialSSL Wildcard, InstantSSL Pro, PremiumSSL Wildcard,
     * EV SSL, EV Multi Domain SSL, Multi Domain SSL, PositiveSSL Multi Domain,
     * Unified Communications
     */
    public function parseCSR(
        string $csr,
        ?string $certificateType = null
    ): string|array {
        $data = [
            "csr" => $csr,
            "CertificateType" => $certificateType,
        ];
        return $this->post($this->command . __FUNCTION__, $data);
    }

    /**
     * Gets approver email list for the requested certificate
     *
     * @param string $domainName Domain name to get the list for
     * @param string $certificateType Type of SSL certificate
     */
    public function getApproverEmailList(
        string $domainName,
        string $certificateType
    ): string|array {
        $data = [
            "DomainName" => $domainName,
            "CertificateType" => $certificateType,
        ];
        return $this->post($this->command . __FUNCTION__, $data);
    }

    /**
     * Activates a purchased and non-activated SSL certificate
     *
     * Collects and validates certificate request data and submits it to Comodo.
     *
     * @param int $certificateId Unique identifier of SSL certificate to be activated
     * @param string $csr Certificate Signing Request (CSR)
     * @param string $adminEmailAddress Email address to send signed SSL certificate file to
     * @param string|null $webServerType Server software where SSL will be installed
     *
     * Supported web server types: apacheopenssl, apachessl, apacheraven, apachessleay,
     * apache2, apacheapachessl, tomcat, cpanel, ipswitch, plesk, weblogic, website,
     * webstar, nginx, iis, iis4, iis5, c2net, ibmhttp, iplanet, domino, dominogo4625,
     * dominogo4626, netscape, zeusv3, cobaltseries, ensim, hsphere, other
     *
     * @note Command can be run on purchased and non-activated SSLs in "Newpurchase" or "Newrenewal" status
     * @note Only supported products can be activated. See create API to learn supported products
     * @note Sandbox limitation: Activation process works for all certificates. However, an actual test certificate will not be returned for OV and EV certificates
     * @note This method is not yet implemented
     */
    public function activate(
        int $certificateId,
        string $csr,
        string $adminEmailAddress,
        ?string $webServerType = null
    ): bool {
        return false;
    }

    /**
     * Resends the approver email
     *
     * @param int $certificateId Unique certificate ID from ssl.create command
     */
    public function resendApproverEmail(int $certificateId): string|array
    {
        return $this->get($this->command . __FUNCTION__, [
            "CertificateID" => $certificateId,
        ]);
    }

    /**
     * Retrieves information about the requested SSL certificate
     *
     * @param int $certificateId Unique ID of the SSL certificate
     * @param string|null $returnCertificate Flag for returning certificate in response
     * @param string|null $returnType Type of returned certificate: Individual (X.509) or PKCS7
     */
    public function getInfo(
        int $certificateId,
        ?string $returnCertificate = null,
        ?string $returnType = null
    ): string|array {
        $data = [
            "CertificateID" => $certificateId,
            "Returncertificate" => $returnCertificate,
            "Returntype" => $returnType,
        ];
        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Renews an SSL certificate
     *
     * @param int $certificateId Unique ID of the SSL certificate to renew
     * @param int $years Number of years to renew for (1-2)
     * @param string $sslType SSL product name (see supported types in create method)
     * @param string|null $promotionCode Promotional (coupon) code for the certificate
     */
    public function renew(
        int $certificateId,
        int $years,
        string $sslType,
        ?string $promotionCode = null
    ): string|array {
        $data = [
            "CertificateID" => $certificateId,
            "Years" => $years,
            "SSLType" => $sslType,
            "PromotionCode" => $promotionCode,
        ];
        return $this->post($this->command . __FUNCTION__, $data);
    }

    /**
     * Initiates creation of a new certificate version of an active SSL certificate
     *
     * Collects and validates new certificate request data and submits it to Comodo.
     *
     * @param int $certificateId Unique identifier of SSL certificate to be reissued
     * @param string $csr Certificate Signing Request (CSR)
     * @param string $adminEmailAddress Email address to send signed SSL certificate file to
     * @param string|null $webServerType Server software where SSL will be installed
     *
     * @note This method is not yet implemented
     */
    public function reissue(
        int $certificateId,
        string $csr,
        string $adminEmailAddress,
        ?string $webServerType = null
    ): bool {
        return false;
    }

    /**
     * Resends the fulfilment email containing the certificate
     *
     * @param int $certificateId Unique certificate ID from ssl.create command
     */
    public function resendFulfillmentEmail(int $certificateId): string|array
    {
        return $this->get($this->command . __FUNCTION__, [
            "CertificateID" => $certificateId,
        ]);
    }

    /**
     * Purchases more add-on domains for already purchased certificate
     *
     * @param int $certificateId ID of the certificate to purchase more add-on domains for
     * @param int $numberOfSansToAdd Number of add-on domains to be ordered
     */
    public function purchaseMoreSans(int $certificateId, int $numberOfSansToAdd): string|array
    {
        return $this->get($this->command . __FUNCTION__, [
            "CertificateID" => $certificateId,
            "NumberOfSANSToAdd" => $numberOfSansToAdd,
        ]);
    }

    /**
     * Revokes a re-issued SSL certificate (Comodo certificates only)
     *
     * @param int $certificateId ID of the certificate to revoke
     * @param string $certificateType Type of SSL certificate
     *
     * Supported certificate types: InstantSSL, PositiveSSL, PositiveSSL Wildcard,
     * EssentialSSL, EssentialSSL Wildcard, InstantSSL Pro, PremiumSSL Wildcard,
     * EV SSL, EV Multi Domain SSL, Multi Domain SSL, PositiveSSL Multi Domain,
     * Unified Communications
     */
    public function revokeCertificate(int $certificateId, string $certificateType): string|array
    {
        return $this->get($this->command . __FUNCTION__, [
            "CertificateID" => $certificateId,
            "CertificateType" => $certificateType,
        ]);
    }

    /**
     * Sets new domain control validation (DCV) method for a certificate
     *
     * This method serves as a 'retry' mechanism for DCV validation.
     *
     * @note This method is not yet implemented
     */
    public function editDcvMethod(): bool
    {
        return false;
    }
}
