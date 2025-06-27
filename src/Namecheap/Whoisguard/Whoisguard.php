<?php

declare(strict_types=1);

namespace Namecheap\Whoisguard;

use Namecheap\Api;
/**
 * Namecheap API wrapper - WhoisGuard privacy protection management
 *
 * @author Saddam Hossain <saddamrhossain@gmail.com>
 * @version 2.0
 */
final class Whoisguard extends Api
{


    protected string $command = 'namecheap.whoisguard.';

    /**
     * Changes WhoisGuard email address
     *
     * @param int $whoisguardId The unique WhoisGuardID that you wish to change
     */
    public function changeEmailAddress(int $whoisguardId): string|array
    {
        return $this->get($this->command . __FUNCTION__, ['WhoisguardID' => $whoisguardId]);
    }

    /**
     * Enables WhoisGuard privacy protection
     *
     * @param int $whoisguardId The unique WhoisGuardID which you get
     * @param string $forwardedToEmail The email address to which WhoisGuard emails are forwarded
     */
    public function enable(int $whoisguardId, string $forwardedToEmail): string|array
    {
        return $this->get($this->command . __FUNCTION__, [
            'WhoisguardID' => $whoisguardId,
            'ForwardedToEmail' => $forwardedToEmail,
        ]);
    }

    /**
     * Disables WhoisGuard privacy protection
     *
     * @param int $whoisguardId The unique WhoisGuardID which has to be disabled
     */
    public function disable(int $whoisguardId): string|array
    {
        return $this->get($this->command . __FUNCTION__, ['WhoisguardID' => $whoisguardId]);
    }

    /**
     * Unallots WhoisGuard privacy protection
     *
     * @param int $whoisguardId The unique WhoisGuardID that has to be unalloted
     */
    public function unallot(int $whoisguardId): string|array
    {
        return $this->get($this->command . __FUNCTION__, ['WhoisguardID' => $whoisguardId]);
    }

    /**
     * Discards WhoisGuard
     *
     * @param int $whoisguardId The WhoisGuardID you wish to discard
     */
    public function discard(int $whoisguardId): string|array
    {
        return $this->get($this->command . __FUNCTION__, ['WhoisguardID' => $whoisguardId]);
    }

    /**
     * Allots WhoisGuard
     *
     * @param int $whoisguardId The unique WhoisGuardID
     * @param string $domainName Domain that you wish to allot WhoisGuard to
     * @param string|null $forwardedToEmail The email to which you wish to forward your WhoisGuard emails
     * @param string|null $enableWg Possible values: True and False (default: False)
     */
    public function allot(
        int $whoisguardId,
        string $domainName,
        ?string $forwardedToEmail = null,
        ?string $enableWg = null
    ): string|array {
        $data = [
            'WhoisguardId' => $whoisguardId,
            'DomainName' => $domainName,
            'ForwardedToEmail' => $forwardedToEmail,
            'EnableWG' => $enableWg,
        ];
        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Gets the list of WhoisGuard privacy protection
     *
     * @param string|null $listType Possible values: ALL, ALLOTED, FREE, DISCARD (default: ALL)
     * @param int|null $page Page to return (default: 1)
     * @param int|null $pageSize Number of WhoisGuard to be listed on a page (min: 2, max: 100)
     */
    public function getList(
        ?string $listType = null,
        ?int $page = null,
        ?int $pageSize = null
    ): string|array {
        $data = [
            'ListType' => $listType,
            'Page' => $page,
            'PageSize' => $pageSize,
        ];
        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Renews WhoisGuard privacy protection
     *
     * @param int $whoisguardId WhoisGuardID to renew
     * @param int $years Number of years to renew (default: 1)
     * @param string|null $promotionCode Promotional (coupon) code for renewing the WhoisGuard
     */
    public function renew(
        int $whoisguardId,
        int $years = 1,
        ?string $promotionCode = null
    ): string|array {
        $data = [
            'WhoisguardID' => $whoisguardId,
            'Years' => $years,
            'PromotionCode' => $promotionCode,
        ];
        return $this->get($this->command . __FUNCTION__, $data);
    }

}