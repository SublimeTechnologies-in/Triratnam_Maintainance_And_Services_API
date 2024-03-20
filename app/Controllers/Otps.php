<?php
namespace App\Controllers;

use App\Models\Otp;
use CodeIgniter\RESTful\ResourceController;

class Otps extends ResourceController
{
    public function verify()
    {
        $post = $this->request->getPost();
        return $this->respond($this->verifyOtpDetails($post));
    }
    public function send()
    {
        // Access the post request and extract contact no.
        $contact = $this->request->getPost('contact');

        // Check if contact is provided
        if (empty($contact)) {
            return $this->respond(['success' => false, 'message' => 'Contact number is required.']);
        }

        // Generate OTP between 1000 to 9999
        $otp = rand(1000, 9999);

        // otp send limit initial value
        $limit = 1;

        // Save to table if there is no OTP in table for the same number or if the OTP is old more than 2 minutes
        $otpModel = new Otp();
        $existingOtp = $otpModel->where(['contact' => $contact])->first();

        // Check if OTP limit exceeded for today
        if ($existingOtp && $existingOtp['otp_limit'] >= 4 && strtotime(date('Y-m-d')) == strtotime(date('Y-m-d', strtotime($existingOtp['updated_at'])))) {
            $updatedAt = strtotime(date('Y-m-d', strtotime($existingOtp['updated_at'])));
            $createdAt = strtotime(date('Y-m-d', strtotime($existingOtp['created_at'])));
            if ($createdAt == $updatedAt) {
                return $this->respond(['success' => false, 'message' => 'OTP send Limit Exceeded for today!']);
            } else {
                $otpModel->where(['contact' => $contact])->delete();
            }
        }

        if ($existingOtp && (time() - strtotime($existingOtp['updated_at'])) < 120) {
            // Existing OTP found and it's less than 2 minutes old
            return $this->respond(['success' => false, 'message' => 'OTP already sent recently. Please wait before requesting again.']);
        }

        if ($existingOtp) {
            $limit += $existingOtp['otp_limit'];
        }

        //max 3 otp can send

        $otpData = [
            'contact' => $contact,
            'otp' => $otp,
            'otp_limit' => $limit
        ];

        if ($existingOtp) {
            $otpModel->set($otpData)->where('contact', $otpData['contact'])->update();
        } else {
            $otpModel->save($otpData);
        }
        // Return response
        return $this->respond([
            'success' => true,
            'message' => 'OTP sent successfully. ' . $otp,
        ]);
    }

    /**
     * Method verifyOtpDetails
     * this function will return is otp valid or not message
     * @param array $data [contact,otp]
     *
     * @return array
     */
    function verifyOtpDetails(array $data): array
    {
        // Access the post request and extract contact no. and OTP
        $contact = $data['contact'];
        $enteredOtp = $data['otp'];
        // Check if contact and OTP are provided
        if (empty($contact) || empty($enteredOtp)) {
            return ['success' => false, 'message' => 'Contact number and OTP are required.'];
        }
        // Check if OTP exists in the database
        $otpModel = new Otp();
        $storedOtp = $otpModel->where(['contact' => $contact])->first();

        if (!$storedOtp) {
            return ['success' => false, 'message' => 'Invalid OTP.'];
        }

        // Check if the OTP is older than 2 minutes
        $otpUpdateTime = strtotime($storedOtp['updated_at']);
        $currentTimestamp = time();

        if (($currentTimestamp - $otpUpdateTime) > 120) {
            return ['success' => false, 'message' => 'OTP has expired.'];
        }

        // Check if the entered OTP matches the stored OTP
        if ($enteredOtp != $storedOtp['otp']) {
            return ['success' => false, 'message' => 'Incorrect OTP.'];
        }
        // Clear the OTP from the database after successful verification
        $otpModel->where(['contact' => $contact])->delete();
        return [
            'success' => true,
            'message' => 'OTP verification successful.',
        ];
    }
}