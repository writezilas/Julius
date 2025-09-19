<?php

namespace App\Helpers;

class PaymentHelper
{
    /**
     * Get the appropriate payment details based on till number availability
     * 
     * @param object|array $businessProfile The user's business profile (decoded JSON)
     * @param object $user The user object (for fallback name)
     * @return array Array containing payment_name and payment_number
     */
    public static function getPaymentDetails($businessProfile, $user = null)
    {
        // Convert to object if array
        if (is_array($businessProfile)) {
            $businessProfile = (object) $businessProfile;
        }

        // If businessProfile is null, return defaults
        if (!$businessProfile) {
            return [
                'payment_name' => $user->name ?? 'Not Set',
                'payment_number' => 'Not Set'
            ];
        }

        // Check if Till details are available and not blank/null
        $tillNumber = isset($businessProfile->mpesa_till_no) ? trim($businessProfile->mpesa_till_no) : '';
        $tillName = isset($businessProfile->mpesa_till_name) ? trim($businessProfile->mpesa_till_name) : '';

        // If both Till Number and Till Name are not blank/null, use Till details
        if (!empty($tillNumber) && !empty($tillName)) {
            return [
                'payment_name' => $tillName,
                'payment_number' => $tillNumber
            ];
        }

        // If either Till Number or Till Name is blank/null, use regular M-Pesa details
        $mpesaName = isset($businessProfile->mpesa_name) ? trim($businessProfile->mpesa_name) : '';
        $mpesaNumber = isset($businessProfile->mpesa_no) ? trim($businessProfile->mpesa_no) : '';

        return [
            'payment_name' => !empty($mpesaName) ? $mpesaName : ($user->name ?? 'Not Set'),
            'payment_number' => !empty($mpesaNumber) ? $mpesaNumber : 'Not Set'
        ];
    }

    /**
     * Get payment details for a specific user
     * 
     * @param \App\Models\User $user The user object
     * @return array Array containing payment_name and payment_number
     */
    public static function getUserPaymentDetails($user)
    {
        $businessProfile = null;
        
        if ($user && $user->business_profile) {
            $businessProfile = json_decode($user->business_profile);
        }

        return self::getPaymentDetails($businessProfile, $user);
    }
}
