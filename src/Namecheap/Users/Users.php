<?php

declare(strict_types=1);

namespace Namecheap\Users;

use Namecheap\Api;
/**
 * Namecheap API wrapper - User management
 *
 * @author Saddam Hossain <saddamrhossain@gmail.com>
 * @version 2.0
 */
final class Users extends Api
{


    protected string $command = 'namecheap.users.';

    /**
     * Returns pricing information for a requested product type
     *
     * @param string $productType Product type to get pricing information for (default: DOMAIN)
     * @param string|null $productCategory Specific category within a product type
     * @param string|null $promotionCode Promotional (coupon) code for the user
     * @param string|null $actionName Specific action within a product type
     * @param string|null $productName The name of the product within a product type
     *
     * Supported ProductType values:
     * - DOMAIN: ActionName -> REGISTER, RENEW, REACTIVATE, TRANSFER
     * - SSLCERTIFICATE: ActionName -> PURCHASE, RENEW
     * - WHOISGUARD: ActionName -> PURCHASE, RENEW
     */
    public function getPricing(
        string $productType = 'DOMAIN',
        ?string $productCategory = null,
        ?string $promotionCode = null,
        ?string $actionName = null,
        ?string $productName = null
    ): string|array {
        $data = [
            'ProductType' => $productType,
            'ProductCategory' => $productCategory,
            'PromotionCode' => $promotionCode,
            'ActionName' => $actionName,
            'ProductName' => $productName,
        ];
        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Gets information about funds in the user's account
     *
     * Returns the following information: Available Balance, Account Balance,
     * Earned Amount, Withdrawable Amount and Funds Required for AutoRenew.
     */
    public function getBalances(): string|array
    {
        return $this->get($this->command . __FUNCTION__);
    }

    /**
     * Changes password of the particular user's account
     *
     * @param string $oldPasswordOrResetCode Old password of the user or password reset code
     * @param string $newPassword New password of the user
     * @param bool $resetPass Whether this is a password reset (default: false)
     *
     * @note When using reset code, UserName should be omitted for the API call
     */
    public function changePassword(
        string $oldPasswordOrResetCode,
        string $newPassword,
        bool $resetPass = false
    ): string|array {
        if ($resetPass) {
            $data = ['ResetCode' => $oldPasswordOrResetCode, 'NewPassword' => $newPassword];
            $this->userName = null; // UserName should be omitted for this API call
        } else {
            $data = ['OldPassword' => $oldPasswordOrResetCode, 'NewPassword' => $newPassword];
        }
        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Updates user account information for the particular user
     *
     * @param array $param User information array with the following keys:
     *   Required: firstName, lastName, address1, city, stateProvince, zip,
     *             country, emailAddress, phone
     *   Optional: jobTitle, organization, address2, phoneExt, fax
     *
     * @throws \Exception When required fields are missing
     *
     * @note Phone and Fax should be in format +NNN.NNNNNNNNNN
     * @note Country should be a two-letter country code
     */
    public function update(array $param): string|array
    {
        $requiredParams = ['FirstName', 'LastName', 'Address1', 'City', 'StateProvince', 'Zip', 'Country', 'EmailAddress', 'Phone'];

        $data = [
            'FirstName' => $param['firstName'] ?? null,
            'LastName' => $param['lastName'] ?? null,
            'Address1' => $param['address1'] ?? null,
            'City' => $param['city'] ?? null,
            'StateProvince' => $param['stateProvince'] ?? null,
            'Zip' => $param['zip'] ?? null,
            'Country' => $param['country'] ?? null,
            'EmailAddress' => $param['emailAddress'] ?? null,
            'Phone' => $param['phone'] ?? null,
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
     * Creates a request to add funds through a credit card
     *
     * @param string $username Username to add funds to
     * @param string $paymentType Allowed payment value: Creditcard
     * @param float $amount Amount to add
     * @param string $returnUrl Valid URL to redirect user after payment completion
     *
     * Process overview:
     * 1. Call this method to create an add funds request
     * 2. If successful, you receive TokenId, ReturnURL and RedirectURL in response
     * 3. Redirect customer to RedirectURL for credit card details submission
     * 4. After payment processing, user is redirected to your specified ReturnURL
     *
     * @note The TokenId can be used to check if funds were added successfully
     */
    public function createAddFundsRequest(
        string $username,
        string $paymentType,
        float $amount,
        string $returnUrl
    ): string|array {
        $this->userName = null; // make the user name null by default
        $data = [
            'username' => $username,
            'paymentType' => $paymentType,
            'amount' => $amount,
            'returnUrl' => $returnUrl,
        ];
        return $this->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Gets the status of add funds request
     *
     * @param string $tokenId Unique ID received after calling createAddFundsRequest method
     */
    public function getAddFundsStatus(string $tokenId): string|array
    {
        return $this->get($this->command . __FUNCTION__, ['TokenId' => $tokenId]);
    }

    /**
     * Creates a new account at NameCheap under this ApiUser
     *
     * @param array $param User information array with the following keys:
     *   Required: newUserName, newUserPassword, emailAddress, firstName, lastName,
     *             acceptTerms, address1, city, stateProvince, zip, country, phone
     *   Optional: ignoreDuplicateEmailAddress, acceptNews, jobTitle, organization,
     *             address2, phoneExt, fax
     *
     * @throws \Exception When required fields are missing
     *
     * @note Phone and Fax should be in format +NNN.NNNNNNNNNN
     * @note Country should be a two-letter country code
     * @note AcceptTerms should be 1 for user account creation
     * @note AcceptNews: 1 to receive newsletters, 0 otherwise
     * @note IgnoreDuplicateEmailAddress defaults to "Yes"
     */
    public function create(array $param): string|array
    {
        $requiredParams = ['NewUserName', 'NewUserPassword', 'EmailAddress', 'FirstName', 'LastName', 'AcceptTerms', 'Address1', 'City', 'StateProvince', 'Zip', 'Country', 'Phone'];

        $data = [
            'NewUserName' => $param['newUserName'] ?? null,
            'NewUserPassword' => $param['newUserPassword'] ?? null,
            'EmailAddress' => $param['emailAddress'] ?? null,
            'FirstName' => $param['firstName'] ?? null,
            'LastName' => $param['lastName'] ?? null,
            'AcceptTerms' => $param['acceptTerms'] ?? null,
            'Address1' => $param['address1'] ?? null,
            'City' => $param['city'] ?? null,
            'StateProvince' => $param['stateProvince'] ?? null,
            'Zip' => $param['zip'] ?? null,
            'Country' => $param['country'] ?? null,
            'Phone' => $param['phone'] ?? null,
            'IgnoreDuplicateEmailAddress' => $param['ignoreDuplicateEmailAddress'] ?? null,
            'AcceptNews' => $param['acceptNews'] ?? null,
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
     * Validates the username and password of user accounts created via API
     *
     * @param string $password Password of the user account
     *
     * @note You cannot use this command to validate user accounts created directly at namecheap.com
     * @note Use the global parameter UserName to specify the username of the user account
     */
    public function login(string $password): string|array
    {
        return $this->get($this->command . __FUNCTION__, ['Password' => $password]);
    }

    /**
     * Sends a password reset link to the end user's profile email
     *
     * @param string $findByValue The username/email address/domain of the user
     * @param string $findBy Search method: EMAILADDRESS, DOMAINNAME, USERNAME (default: EMAILADDRESS)
     * @param string|null $emailFromName Override default sender name (default: namecheap.com)
     * @param string|null $emailFrom Override default sender email (default: support@namecheap.com)
     * @param string|null $urlPattern Override default URL pattern (default: http://namecheap.com [RESETCODE])
     *
     * @note UserName should be omitted for this API call. All other Global parameters must be included.
     * @note The end user needs to click on the emailed link to reset password
     */
    public function resetPassword(
        string $findByValue,
        string $findBy = 'EMAILADDRESS',
        ?string $emailFromName = null,
        ?string $emailFrom = null,
        ?string $urlPattern = null
    ): string|array {
        $this->userName = null; // UserName should be omitted for this API call

        $data = [
            'FindBy' => $findBy,
            'FindByValue' => $findByValue,
            'EmailFromName' => $emailFromName,
            'EmailFrom' => $emailFrom,
            'URLPattern' => $urlPattern,
        ];

        return $this->get($this->command . __FUNCTION__, $data);
    }
}
