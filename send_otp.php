<?php
// Add this function to send OTP via email gateway
function sendSMSViaCarrier($phone, $message) {
    // Clean phone number
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Detect carrier based on prefix (simplified for Uganda)
    $prefix = substr($phone, -9, 3);
    
    $carrier = 'mtn'; // default
    if (in_array($prefix, ['70', '71'])) {
        $carrier = 'mtn';
    } elseif (in_array($prefix, ['75', '74'])) {
        $carrier = 'airtel';
    }
    
    $gateways = [
        'mtn' => '@sms.mtn.co.ug',
        'airtel' => '@sms.airtel.co.ug'
    ];
    
    $to = $phone . ($gateways[$carrier] ?? '@sms.mtn.co.ug');
    $subject = "MotherCare OTP";
    $headers = "From: noreply@mothercare.com\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// In your existing code, replace SMS sending with:
if ($method == 'sms') {
    if (sendSMSViaCarrier($user['phone'], "Your MotherCare OTP is: $otp")) {
        $sent = true;
        $message = "OTP sent to your phone";
    } else {
        $message = "Failed to send SMS. Please try email instead.";
    }
}