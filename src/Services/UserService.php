<?php

declare(strict_types = 1);

namespace Namecheap\Services;

use Namecheap\ContactBuilder;
use Namecheap\Enums\ServiceClass;
use Namecheap\Response\NamecheapResponse;
use Namecheap\Response\NamecheapResponseParser;
use Namecheap\Traits\FieldValidationTrait;

/**
 * Namecheap User Service
 *
 * @author  Joeseph Chen <joeseph.chens@gmail.com>
 * @version 1.0
 */
final class UserService extends ApiService
{
    use FieldValidationTrait;

    private string $command = 'namecheap.users.';

    /**
     * Returns pricing information for a requested product type
     *
     * @param string      $productType     Product type to get pricing information for (default: DOMAIN)
     * @param string|null $productCategory Specific category within a product type
     * @param string|null $promotionCode   Promotional (coupon) code for the user
     * @param string|null $actionName      Specific action within a product type
     * @param string|null $productName     The name of the product within a product type
     *
     * Supported ProductType values:
     * - DOMAIN: ActionName -> REGISTER, RENEW, REACTIVATE, TRANSFER
     * - SSLCERTIFICATE: ActionName -> PURCHASE, RENEW
     * - WHOISGUARD: ActionName -> PURCHASE, RENEW
     *
     * @return NamecheapResponse
     */
    public function getPricing(
        string  $productType = 'DOMAIN',
        ?string $productCategory = null,
        ?string $promotionCode = null,
        ?string $actionName = null,
        ?string $productName = null
    ): NamecheapResponse {
        $data = [
            'ProductType'     => $productType,
            'ProductCategory' => $productCategory,
            'PromotionCode'   => $promotionCode,
            'ActionName'      => $actionName,
            'ProductName'     => $productName,
        ];

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Gets information about funds in the user's account
     *
     * Returns the following information: Available Balance, Account Balance,
     * Earned Amount, Withdrawable Amount and Funds Required for AutoRenew.
     *
     * @return NamecheapResponse
     */
    public function getBalances(): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__);
    }

    /**
     * Changes password of the particular user's account
     *
     * @param string $oldPasswordOrResetCode Old password of the user or password reset code
     * @param string $newPassword            New password of the user
     * @param bool   $resetPass              Whether this is a password reset (default: false)
     *
     * @return NamecheapResponse
     * @note When using reset code, UserName should be omitted for the API call
     */
    public function changePassword(
        string $oldPasswordOrResetCode,
        string $newPassword,
        bool   $resetPass = false
    ): NamecheapResponse {
        if ($resetPass) {
            $data             = ['ResetCode' => $oldPasswordOrResetCode, 'NewPassword' => $newPassword];
            $originalUserName = $this->apiClient->getUserName();

            $this->apiClient->setUserName(null);

            $result = $this->apiClient->get($this->command . __FUNCTION__, $data);

            $this->apiClient->setUserName($originalUserName);

            return $result;
        } else {
            $data = ['OldPassword' => $oldPasswordOrResetCode, 'NewPassword' => $newPassword];

            return $this->apiClient->get($this->command . __FUNCTION__, $data);
        }
    }

    /**
     * Updates user account information for the particular user
     *
     * @param array $param User information array with the following keys:
     *                     Required: firstName, lastName, address1, city, stateProvince, zip,
     *                     country, emailAddress, phone
     *                     Optional: jobTitle, organization, address2, phoneExt, fax
     *
     * @return NamecheapResponse
     * @note Phone and Fax should be in format +NNN.NNNNNNNNNN
     * @note Country should be a two-letter country code
     */
    public function update(array $param): NamecheapResponse
    {
        $contactData = $param;

        if (isset($param['zip'])) $contactData['postalCode'] = $param['zip'];

        if (isset($param['organization'])) $contactData['organizationName'] = $param['organization'];

        $requiredFields = ['firstName', 'lastName', 'address1', 'city', 'stateProvince', 'postalCode', 'country', 'emailAddress', 'phone'];
        $reqFields      = $this->checkRequiredFields($contactData, $requiredFields);

        if (count($reqFields)) {
            $fieldList = implode(', ', $reqFields);

            return NamecheapResponseParser::createErrorResponse(
                $fieldList . ' : these fields are required!',
                'namecheap.users.update',
                0.0,
                2010324
            );
        }

        $data = ContactBuilder::for(ServiceClass::USER)->setContactData($contactData)->build();

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }


    /**
     * Creates a request to add funds through a credit card
     *
     * @param string $username    Username to add funds to
     * @param string $paymentType Allowed payment value: Creditcard
     * @param float  $amount      Amount to add
     * @param string $returnUrl   Valid URL to redirect user after payment completion
     *
     * Process overview:
     * 1. Call this method to create an add funds request
     * 2. If successful, you receive TokenId, ReturnURL and RedirectURL in response
     * 3. Redirect customer to RedirectURL for credit card details submission
     * 4. After payment processing, user is redirected to your specified ReturnURL
     *
     * @return NamecheapResponse
     * @note The TokenId can be used to check if funds were added successfully
     */
    public function createAddFundsRequest(
        string $username,
        string $paymentType,
        float  $amount,
        string $returnUrl
    ): NamecheapResponse {
        $originalUserName = $this->apiClient->getUserName();

        $this->apiClient->setUserName(null);

        $data   = [
            'username'    => $username,
            'paymentType' => $paymentType,
            'amount'      => $amount,
            'returnUrl'   => $returnUrl,
        ];
        $result = $this->apiClient->get($this->command . __FUNCTION__, $data);

        $this->apiClient->setUserName($originalUserName);

        return $result;
    }

    /**
     * Gets the status of add funds request
     *
     * @param string $tokenId Unique ID received after calling createAddFundsRequest method
     *
     * @return NamecheapResponse
     */
    public function getAddFundsStatus(string $tokenId): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, ['TokenId' => $tokenId]);
    }

    /**
     * Creates a new account at NameCheap under this ApiUser
     *
     * @param array $param User information array with the following keys:
     *                     Required: newUserName, newUserPassword, emailAddress, firstName, lastName,
     *                     acceptTerms, address1, city, stateProvince, zip, country, phone
     *                     Optional: ignoreDuplicateEmailAddress, acceptNews, jobTitle, organization,
     *                     address2, phoneExt, fax
     *
     * @return NamecheapResponse
     * @note Phone and Fax should be in format +NNN.NNNNNNNNNN
     * @note Country should be a two-letter country code
     * @note AcceptTerms should be 1 for user account creation
     * @note AcceptNews: 1 to receive newsletters, 0 otherwise
     * @note IgnoreDuplicateEmailAddress defaults to "Yes"
     */
    public function create(array $param): NamecheapResponse
    {
        $contactData = $param;

        if (isset($param['zip'])) $contactData['postalCode'] = $param['zip'];

        if (isset($param['organization'])) $contactData['organizationName'] = $param['organization'];

        $requiredContactFields    = ['firstName', 'lastName', 'address1', 'city', 'stateProvince', 'postalCode', 'country', 'emailAddress', 'phone'];
        $requiredNonContactFields = ['newUserName', 'newUserPassword', 'acceptTerms'];
        $contactMissing           = $this->checkRequiredFields($contactData, $requiredContactFields);
        $nonContactMissing        = $this->checkRequiredFields($param, $requiredNonContactFields);
        $reqFields                = array_merge($contactMissing, $nonContactMissing);

        if (count($reqFields)) {
            $fieldList = implode(', ', $reqFields);

            return NamecheapResponseParser::createErrorResponse(
                $fieldList . ' : these fields are required!',
                'namecheap.users.create',
                0.0,
                2010324
            );
        }

        $contactFields    = ContactBuilder::for(ServiceClass::USER)->setContactData($contactData)->build();
        $nonContactFields = [
            'NewUserName'                 => $param['newUserName'] ?? null,
            'NewUserPassword'             => $param['newUserPassword'] ?? null,
            'AcceptTerms'                 => $param['acceptTerms'] ?? null,
            'IgnoreDuplicateEmailAddress' => $param['ignoreDuplicateEmailAddress'] ?? null,
            'AcceptNews'                  => $param['acceptNews'] ?? null,
        ];
        $data             = array_merge($contactFields, $nonContactFields);

        return $this->apiClient->get($this->command . __FUNCTION__, $data);
    }

    /**
     * Validates the username and password of user accounts created via API
     *
     * @param string $password Password of the user account
     *
     * @return NamecheapResponse
     * @note You cannot use this command to validate user accounts created directly at namecheap.com
     * @note Use the global parameter UserName to specify the username of the user account
     */
    public function login(string $password): NamecheapResponse
    {
        return $this->apiClient->get($this->command . __FUNCTION__, ['Password' => $password]);
    }

    /**
     * Sends a password reset link to the end user's profile email
     *
     * @param string      $findByValue   The username/email address/domain of the user
     * @param string      $findBy        Search method: EMAILADDRESS, DOMAINNAME, USERNAME (default: EMAILADDRESS)
     * @param string|null $emailFromName Override default sender name (default: namecheap.com)
     * @param string|null $emailFrom     Override default sender email (default: support@namecheap.com)
     * @param string|null $urlPattern    Override default URL pattern (default: http://namecheap.com [RESETCODE])
     *
     * @return NamecheapResponse
     * @note UserName should be omitted for this API call. All other Global parameters must be included.
     * @note The end user needs to click on the emailed link to reset password
     */
    public function resetPassword(
        string  $findByValue,
        string  $findBy = 'EMAILADDRESS',
        ?string $emailFromName = null,
        ?string $emailFrom = null,
        ?string $urlPattern = null
    ): NamecheapResponse {
        $originalUserName = $this->apiClient->getUserName();

        $this->apiClient->setUserName(null);

        $data = [
            'FindBy'        => $findBy,
            'FindByValue'   => $findByValue,
            'EmailFromName' => $emailFromName,
            'EmailFrom'     => $emailFrom,
            'URLPattern'    => $urlPattern,
        ];

        $result = $this->apiClient->get($this->command . __FUNCTION__, $data);
        
        $this->apiClient->setUserName($originalUserName);

        return $result;
    }
}