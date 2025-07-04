<?php

declare(strict_types = 1);

namespace Namecheap\Services;

use Namecheap\Response\NamecheapResponse;

/**
 * Namecheap WhoisGuard Service
 *
 * @author  Joeseph Chen <joeseph.chens@gmail.com>
 * @version 1.0
 */
final class WhoisguardService extends ApiService
{
    private string $command = 'namecheap.whoisguard.';

    /**
     * Changes WhoisGuard email address
     *
     * @param int $whoisguardId The unique WhoisGuardID that you wish to change
     *
     * @return NamecheapResponse
     */
    public function changeEmailAddress(int $whoisguardId): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, ['WhoisguardID' => $whoisguardId]);
    }

    /**
     * Enables WhoisGuard privacy protection
     *
     * @param int    $whoisguardId     The unique WhoisGuardID which you get
     * @param string $forwardedToEmail The email address to which WhoisGuard emails are forwarded
     *
     * @return NamecheapResponse
     */
    public function enable(int $whoisguardId, string $forwardedToEmail): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, [
            'WhoisguardID'     => $whoisguardId,
            'ForwardedToEmail' => $forwardedToEmail,
        ]);
    }

    /**
     * Disables WhoisGuard privacy protection
     *
     * @param int $whoisguardId The unique WhoisGuardID which has to be disabled
     *
     * @return NamecheapResponse
     */
    public function disable(int $whoisguardId): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, ['WhoisguardID' => $whoisguardId]);
    }

    /**
     * Unallots WhoisGuard privacy protection
     *
     * @param int $whoisguardId The unique WhoisGuardID that has to be unalloted
     *
     * @return NamecheapResponse
     */
    public function unallot(int $whoisguardId): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, ['WhoisguardID' => $whoisguardId]);
    }

    /**
     * Discards WhoisGuard
     *
     * @param int $whoisguardId The WhoisGuardID you wish to discard
     *
     * @return NamecheapResponse
     */
    public function discard(int $whoisguardId): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, ['WhoisguardID' => $whoisguardId]);
    }

    /**
     * Allots WhoisGuard
     *
     * @param int         $whoisguardId     The unique WhoisGuardID
     * @param string      $domainName       Domain that you wish to allot WhoisGuard to
     * @param string|null $forwardedToEmail The email to which you wish to forward your WhoisGuard emails
     * @param string|null $enableWg         Possible values: True and False (default: False)
     *
     * @return NamecheapResponse
     */
    public function allot(
        int     $whoisguardId,
        string  $domainName,
        ?string $forwardedToEmail = null,
        ?string $enableWg = null
    ): NamecheapResponse {
        $data = [
            'WhoisguardID'     => $whoisguardId,
            'DomainName'       => $domainName,
            'ForwardedToEmail' => $forwardedToEmail,
            'EnableWG'         => $enableWg,
        ];

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Gets the list of WhoisGuard privacy protection
     *
     * @param string|null $listType Possible values: ALL, ALLOTED, FREE, DISCARD (default: ALL)
     * @param int|null    $page     Page to return (default: 1)
     * @param int|null    $pageSize Number of WhoisGuard to be listed on a page (min: 2, max: 100)
     *
     * @return NamecheapResponse
     */
    public function getList(
        ?string $listType = null,
        ?int    $page = null,
        ?int    $pageSize = null
    ): NamecheapResponse {
        $data = [
            'ListType' => $listType,
            'Page'     => $page,
            'PageSize' => $pageSize,
        ];

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Renews WhoisGuard privacy protection
     *
     * @param int         $whoisguardId  WhoisGuardID to renew
     * @param int         $years         Number of years to renew (default: 1)
     * @param string|null $promotionCode Promotional (coupon) code for renewing the WhoisGuard
     *
     * @return NamecheapResponse
     */
    public function renew(
        int     $whoisguardId,
        int     $years = 1,
        ?string $promotionCode = null
    ): NamecheapResponse {
        $data = [
            'WhoisguardID'  => $whoisguardId,
            'Years'         => $years,
            'PromotionCode' => $promotionCode,
        ];

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }
}