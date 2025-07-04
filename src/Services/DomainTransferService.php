<?php

declare(strict_types = 1);

namespace Namecheap\Services;

use Namecheap\Response\NamecheapResponse;

/**
 * Namecheap Domain Transfer Service
 *
 * @author  Joeseph Chen <joeseph.chens@gmail.com>
 * @version 1.0
 */
final class DomainTransferService extends ApiService
{
    private string $command = 'namecheap.domains.transfer.';

    /**
     * Transfers a domain to Namecheap
     *
     * @param string      $domainName        Domain name to transfer
     * @param int         $years             Number of years to renew after a successful transfer
     * @param string      $eppCode           EPP code required for transferring most TLDs
     * @param string|null $promotionCode     Promotional (coupon) code for transfer
     * @param string|null $addFreeWhoisguard Adds free Whoisguard (default: Yes)
     * @param string|null $wgEnable          Enables free WhoisGuard (default: No)
     *
     * @return NamecheapResponse
     * @note Supported TLDs: .biz, .ca, .cc, .co, .co.uk, .com, .com.es, .com.pe,
     *       .es, .in, .info, .me, .me.uk, .mobi, .net, .net.pe, .nom.es, .org,
     *       .org.es, .org.pe, .org.uk, .pe, .tv, .us
     */
    public function create(
        string  $domainName,
        int     $years,
        string  $eppCode,
        ?string $promotionCode = null,
        ?string $addFreeWhoisguard = null,
        ?string $wgEnable = null
    ): NamecheapResponse {
        $data = [
            'DomainName'        => $domainName,
            'Years'             => $years,
            'EPPCode'           => $eppCode,
            'PromotionCode'     => $promotionCode,
            'AddFreeWhoisguard' => $addFreeWhoisguard,
            'WGEnable'          => $wgEnable,
        ];

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Gets the status of a particular transfer
     *
     * @param int $transferId The unique Transfer ID received after placing a transfer request
     *
     * @return NamecheapResponse
     */
    public function getStatus(int $transferId): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, ['TransferID' => $transferId]);
    }

    /**
     * Updates the status of a particular transfer
     *
     * Allows you to re-submit the transfer after releasing the registry lock.
     *
     * @param int    $transferId The unique Transfer ID received after placing a transfer request
     * @param string $resubmit   The value 'true' resubmits the transfer
     *
     * @return NamecheapResponse
     */
    public function updateStatus(int $transferId, string $resubmit): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, [
            'TransferID' => $transferId,
            'Resubmit'   => $resubmit,
        ]);
    }

    /**
     * Gets the list of domain transfers
     *
     * @param string|null $listType   Filter type: ALL, INPROGRESS, CANCELLED, COMPLETED (default: ALL)
     * @param string|null $searchTerm Domain name to search for
     * @param int|null    $page       Page to return (default: 1)
     * @param int|null    $pageSize   Number of transfers per page, min 10, max 100 (default: 10)
     * @param string|null $sortBy     Sort order: DOMAINNAME, DOMAINNAME_DESC, TRANSFERDATE,
     *                                TRANSFERDATE_DESC, STATUSDATE, STATUSDATE_DESC (default: DOMAINNAME)
     *
     * @return NamecheapResponse
     */
    public function getList(
        ?string $listType = null,
        ?string $searchTerm = null,
        ?int    $page = null,
        ?int    $pageSize = null,
        ?string $sortBy = null
    ): NamecheapResponse {
        $data = [
            'ListType'   => $listType,
            'SearchTerm' => $searchTerm,
            'Page'       => $page,
            'PageSize'   => $pageSize,
            'SortBy'     => $sortBy,
        ];

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }
}