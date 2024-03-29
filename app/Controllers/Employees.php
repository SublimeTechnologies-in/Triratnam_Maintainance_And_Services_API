<?php

namespace App\Controllers;

use App\Models\User;
use CodeIgniter\RESTful\ResourceController;

class Employees extends ResourceController
{
    public function get()
    {
        if ($this->request->user->user_type != "admin") {
            return $this->respond(['success' => false, 'message' => 'Access Deny'], 403);
        }
        $response = ['success' => false, 'message' => 'No Employees Found'];
        $userModel = new User();
        $employees = $userModel->where('user_type !=', 'admin')->findAll();
        if (empty($employees))
            return $this->respond($response);
        return $this->respond(['success' => true, 'data' => $employees, 'message' => '']);
    }

    public function add($id = null)
    {
        if ($this->request->user->user_type != "admin") {
            return $this->respond(['success' => false, 'message' => 'Access Deny'], 403);
        }
        $rules = [
            'name'     => 'required',
            'contact' => 'required|numeric',
            'address' => 'required'
        ];

        // Add password validation if $id is null
        if (!$id) {
            $rules['password'] = 'required';
        }

        if (!$this->validate($rules)) {
            return $this->respond(['message' => $this->validator->getErrors(), 'success' => false]);
        }

        $contact = $this->request->getPost('contact');

        $userModel = new User();

        // Check if contact already exists, excluding the current user being updated
        if ($id) {
            $existingEmployee = $userModel
                ->where('contact', $contact)
                ->where('id !=', $id) // Exclude the current user being updated
                ->countAllResults();
        } else {
            $existingEmployee = $userModel
                ->where('contact', $contact)
                ->countAllResults();
        }

        if ($existingEmployee > 0) {
            return $this->respond(['message' => 'Contact already exists', 'success' => false]);
        }

        $userData = [
            'name'     => $this->request->getPost('name'),
            'contact' => $contact,
            'address' => $this->request->getPost('address'),
            // Add user_type only if not updating
            'user_type' => (!$id) ? 'employee' : null
        ];

        if (!$id) {
            // If $id is null, insert a new record
            $userData['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
            try {
                $userModel->insert($userData);
            } catch (\Exception $e) {
                return $this->respond(['message' => 'Failed to add user: ' . $e->getMessage(), 'success' => false]);
            }
        } else {
            // If $id is not null, update the existing record
            try {
                $userModel->update($id, $userData);
            } catch (\Exception $e) {
                return $this->respond(['message' => 'Failed to update user: ' . $e->getMessage(), 'success' => false]);
            }
        }

        return $this->respond(['success' => true, 'message' => 'Employee ' . ($id ? 'updated' : 'added') . ' successfully']);
    }





    public function delete($id = null)
    {
        if ($this->request->user->user_type != "admin") {
            return $this->respond(['success' => false, 'message' => 'Access Deny'], 403);
        }
        if ($id === null) {
            return $this->failValidationError('Employee ID is required for deletion');
        }

        $userModel = new User();
        $user = $userModel->where('user_type !=', 'admin')->find($id);

        if (!$user) {
            return $this->respond(['success' => false, 'message' => 'Employee not found']);
        }

        $userModel->delete($id);

        return $this->respondDeleted(['success' => true, 'message' => 'Employee deleted successfully']);
    }

    public function undoDelete($id = null)
    {
        if ($this->request->user->user_type != "admin") {
            return $this->respond(['success' => false, 'message' => 'Access Deny'], 403);
        }
        if ($id === null) {
            return $this->respond(['message' => 'Employee ID is required', 'success' => false]);
        }

        $userModel = new User();
        $user = $userModel->onlyDeleted()->find($id);

        if (!$user) {
            return $this->respond(['message' => 'Deleted employee not found', 'success' => false]);
        }

        // Restore the deleted employee
        $db = db_connect();
        $db->table('users')->where('id', $id)->update(["deleted_at" => NULL]);

        return $this->respond(['success' => true, 'message' => 'Employee undeleted successfully']);
    }
}
