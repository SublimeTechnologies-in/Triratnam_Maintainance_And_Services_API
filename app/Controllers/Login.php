<?php

namespace App\Controllers;

use App\Libraries\JwtService;
use App\Models\User;
use CodeIgniter\RESTful\ResourceController;

class Login extends ResourceController
{
    public function loginCheck()
    {
        $contact = $this->request->getPost('contact');
        $password = $this->request->getPost('password');

        // Example authentication logic
        if ($user = $this->authenticateUser($contact, $password)) {
            // Authentication successful, generate tokens
            $userDetails = [];
            $jwtService = new JwtService();
            $userData = [
                'id' => $user['id'],
                'contact' => $user['contact'],
                'name' => $user['name'],
                'user_type' => $user['user_type']
            ];
            $accessToken = $jwtService->generateAccessToken($userData);
            $refreshToken = $jwtService->generateRefreshToken($userData);

            return $this->respond([
                'success' => true,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'user_data' => $userData
            ]);
        } else {
            return $this->respond(['success' => false, 'message' => 'invalid Credentials']);
        }
    }

    protected function authenticateUser($contact, $password)
    {
        $credentialModel = new User();
        $user = $credentialModel->where('contact', $contact)->first();
        if (!$user || (md5($password) != $user['password'])) {
            return false;
        }
        return $user;
    }
}
