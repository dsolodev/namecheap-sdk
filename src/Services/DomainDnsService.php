<?php

declare(strict_types = 1);

namespace Namecheap\Services;

use Namecheap\Response\NamecheapResponse;

/**
 * Namecheap Domain DNS Service
 *
 * @author  Joeseph Chen <joeseph.chens@gmail.com>
 * @version 1.0
 */
final class DomainDnsService extends ApiService
{
    private string $command = 'namecheap.domains.dns.';

    /**
     * Sets domain to use default DNS servers
     *
     * Required for free services like Host record management, URL forwarding,
     * email forwarding, dynamic DNS and other value added services.
     *
     * @param string $sld SLD (Second Level Domain) of the domain name
     * @param string $tld TLD (Top Level Domain) of the domain name
     *
     * @return NamecheapResponse
     */
    public function setDefault(string $sld, string $tld): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, ['SLD' => $sld, 'TLD' => $tld]);
    }

    /**
     * Sets domain to use custom DNS servers
     *
     * @param string $sld         SLD (Second Level Domain) of the domain name
     * @param string $tld         TLD (Top Level Domain) of the domain name
     * @param string $nameservers Comma-separated list of name servers
     *
     * @return NamecheapResponse
     * @note Services like URL forwarding, Email forwarding, Dynamic DNS will not work
     *       for domains using custom nameservers
     */
    public function setCustom(string $sld, string $tld, string $nameservers): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, [
            'SLD'         => $sld,
            'TLD'         => $tld,
            'Nameservers' => $nameservers,
        ]);
    }

    /**
     * Gets a list of DNS servers associated with the requested domain
     *
     * @param string $sld SLD (Second Level Domain) of the domain name
     * @param string $tld TLD (Top Level Domain) of the domain name
     *
     * @return NamecheapResponse
     */
    public function getList(string $sld, string $tld): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, ['SLD' => $sld, 'TLD' => $tld]);
    }

    /**
     * Retrieves DNS host record settings for the requested domain
     *
     * @param string $sld SLD (Second Level Domain) of the domain name
     * @param string $tld TLD (Top Level Domain) of the domain name
     *
     * @return NamecheapResponse
     */
    public function getHosts(string $sld, string $tld): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, ['SLD' => $sld, 'TLD' => $tld]);
    }

    /**
     * Gets email forwarding settings for the requested domain
     *
     * @param string $domainName Domain name to get settings for
     *
     * @return NamecheapResponse
     */
    public function getEmailForwarding(string $domainName): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, ['DomainName' => $domainName]);
    }

    /**
     * Sets email forwarding for a domain name
     *
     * @param string $domainName Domain name to set settings for
     * @param array  $mailBox    Mailboxes to set forwarding for
     *                           Example: ['mailbox1' => 'info', 'mailbox2' => 'careers']
     * @param array  $forwardTo  Email addresses to forward to
     *                           Example: ['ForwardTo1' => 'info@example.com', 'ForwardTo2' => 'careers@example.com']
     *
     * @return NamecheapResponse
     */
    public function setEmailForwarding(string $domainName, array $mailBox, array $forwardTo): NamecheapResponse
    {
        $data = ['DomainName' => $domainName];

        return $this->apiClient->get($this->command . __FUNCTION__, array_merge($data, $mailBox, $forwardTo));
    }

    /**
     * Sets DNS host records settings for the requested domain
     *
     * @param string      $sld        SLD (Second Level Domain) of the domain
     * @param string      $tld        TLD (Top Level Domain) of the domain
     * @param array       $hostName   Subdomain/hostname to create records for
     * @param array       $recordType Record types: A, AAAA, CNAME, MX, MXE, NS, TXT, URL, URL301, FRAME
     * @param array       $address    URL or IP address values based on record type
     * @param array       $mxPref     MX preference values (applicable for MX records only)
     * @param string|null $emailType  Email type: MXE, MX, FWD, OX (optional)
     * @param array       $ttl        Time to live values (60-60000, default: 1800)
     *
     * @return NamecheapResponse
     * @important Use HTTP POST method when setting more than 10 hostnames.
     *           All host records not included in the API call will be deleted.
     */
    public function setHosts(
        string  $sld,
        string  $tld,
        array   $hostName,
        array   $recordType,
        array   $address,
        array   $mxPref,
        ?string $emailType = null,
        array   $ttl = []
    ): NamecheapResponse {
        $data = [
            'SLD'       => $sld,
            'TLD'       => $tld,
            'EmailType' => $emailType,
        ];

        return $this->apiClient->post($this->command . __FUNCTION__, array_merge($data, $hostName, $recordType, $address, $mxPref, $ttl));
    }
}