<?php

declare(strict_types=1);

namespace Namecheap\Domain;

use Namecheap\Api;
/**
 * Namecheap API wrapper - Domain nameserver management
 *
 * @author Saddam Hossain <saddamrhossain@gmail.com>
 * @version 2.0
 */
final class DomainsNs extends Api
{

    protected string $command = 'namecheap.domains.ns.';

    /**
     * Creates a new nameserver
     *
     * @param string $sld SLD (Second Level Domain) of the domain name
     * @param string $tld TLD (Top Level Domain) of the domain name
     * @param string $nameserver Nameserver to create
     * @param string $ip Nameserver IP address
     */
    public function create(string $sld, string $tld, string $nameserver, string $ip): string|array
    {
        $data = [
            'SLD' => $sld,
            'TLD' => $tld,
            'Nameserver' => $nameserver,
            'IP' => $ip,
        ];
        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Deletes a nameserver associated with the requested domain
     *
     * @param string $sld SLD (Second Level Domain) of the domain name
     * @param string $tld TLD (Top Level Domain) of the domain name
     * @param string $nameserver Nameserver to delete
     */
    public function delete(string $sld, string $tld, string $nameserver): string|array
    {
        $data = [
            'SLD' => $sld,
            'TLD' => $tld,
            'Nameserver' => $nameserver,
        ];
        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Retrieves information about a registered nameserver
     *
     * @param string $sld SLD (Second Level Domain) of the domain name
     * @param string $tld TLD (Top Level Domain) of the domain name
     * @param string $nameserver Nameserver to get information for
     */
    public function getInfo(string $sld, string $tld, string $nameserver): string|array
    {
        $data = [
            'SLD' => $sld,
            'TLD' => $tld,
            'Nameserver' => $nameserver,
        ];
        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Updates the IP address of a registered nameserver
     *
     * @param string $sld SLD (Second Level Domain) of the domain name
     * @param string $tld TLD (Top Level Domain) of the domain name
     * @param string $nameserver Nameserver to update
     * @param string $oldIp Existing IP address
     * @param string $newIp New IP address
     */
    public function update(string $sld, string $tld, string $nameserver, string $oldIp, string $newIp): string|array
    {
        $data = [
            'SLD' => $sld,
            'TLD' => $tld,
            'Nameserver' => $nameserver,
            'OldIP' => $oldIp,
            'IP' => $newIp,
        ];
        return $this->get($this->command . __FUNCTION__, $data);
    }

}

