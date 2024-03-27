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

    function changePassword()
    {
        $credentialModel = new User();
        $post = $this->request->getPost();
        $rules = [
            'old_password' => 'required',
            'new_password' => 'required|min_length[6]',
            'confirm_password' => 'matches[new_password]'
        ];
        $messages = [
            'old_password' => [
                'required' => "Old password is required.",
                'min_length' => "Old password must have at least 6 characters."
            ],
            'new_password' => [
                'required' => "New password is required.",
                'min_length' => "Password must have at least 6 characters.",
                'max_length' => "Password must not exceed 12 characters."
            ],
            'confirm_password' => "Confirmation password does not match the new password"
        ];
        // Checking for validation rules.
        if (!$this->validate($rules, $messages)) {
            return $this->respond([
                'success' => false,
                'message' => $this->validator->getErrors()
            ]);
        }

        $userId = $this->request->user->id;
        $user = $credentialModel->where('id', $userId)->where('password', md5($post['old_password']))->find();
        if (empty($user)) {
            return $this->respond([
                'success' => false,
                'message' => "Old Password Not Match"
            ]);
        }
        $credentialModel->set('password', md5($post['new_password']))->update($userId);
        return $this->respond([
            'success' => true,
            'message' => "Password Changed Successfully"
        ]);
    }
}
