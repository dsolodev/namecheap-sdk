<?php

declare(strict_types = 1);

namespace Namecheap\Services;

use Namecheap\Response\NamecheapResponse;

/**
 * Namecheap Domain Nameserver Service
 *
 * @author  Joeseph Chen <joeseph.chens@gmail.com>
 * @version 1.0
 */
final class DomainNsService extends ApiService
{
    private string $command = 'namecheap.domains.ns.';

    /**
     * Creates a new nameserver
     *
     * @param string $sld        SLD (Second Level Domain) of the domain name
     * @param string $tld        TLD (Top Level Domain) of the domain name
     * @param string $nameserver Nameserver to create
     * @param string $ip         Nameserver IP address
     *
     * @return NamecheapResponse
     */
    public function create(string $sld, string $tld, string $nameserver, string $ip): NamecheapResponse
    {
        $data = [
            'SLD'        => $sld,
            'TLD'        => $tld,
            'Nameserver' => $nameserver,
            'IP'         => $ip,
        ];

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Deletes a nameserver associated with the requested domain
     *
     * @param string $sld        SLD (Second Level Domain) of the domain name
     * @param string $tld        TLD (Top Level Domain) of the domain name
     * @param string $nameserver Nameserver to delete
     *
     * @return NamecheapResponse
     */
    public function delete(string $sld, string $tld, string $nameserver): NamecheapResponse
    {
        $data = [
            'SLD'        => $sld,
            'TLD'        => $tld,
            'Nameserver' => $nameserver,
        ];

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Retrieves information about a registered nameserver
     *
     * @param string $sld        SLD (Second Level Domain) of the domain name
     * @param string $tld        TLD (Top Level Domain) of the domain name
     * @param string $nameserver Nameserver to get information for
     *
     * @return NamecheapResponse
     */
    public function getInfo(string $sld, string $tld, string $nameserver): NamecheapResponse
    {
        $data = [
            'SLD'        => $sld,
            'TLD'        => $tld,
            'Nameserver' => $nameserver,
        ];

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Updates the IP address of a registered nameserver
     *
     * @param string $sld        SLD (Second Level Domain) of the domain name
     * @param string $tld        TLD (Top Level Domain) of the domain name
     * @param string $nameserver Nameserver to update
     * @param string $oldIp      Existing IP address
     * @param string $newIp      New IP address
     *
     * @return NamecheapResponse
     */
    public function update(string $sld, string $tld, string $nameserver, string $oldIp, string $newIp): NamecheapResponse
    {
        $data = [
            'SLD'        => $sld,
            'TLD'        => $tld,
            'Nameserver' => $nameserver,
            'OldIP'      => $oldIp,
            'IP'         => $newIp,
        ];

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }
}