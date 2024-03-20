<?php

namespace App\Controllers;

use App\Models\User;
use CodeIgniter\RESTful\ResourceController;

class Employees extends ResourceController
{
    public function get()
    {
        $response = ['success' => false, 'message' => 'No Employees Found'];
        $userModel = new User();
        $employees = $userModel->where('user_type !=', 'admin')->findAll();
        if (empty($employees))
            return $this->respond($response);
        return $this->respond(['success' => true, 'data' => $employees, 'message' => '']);
    }

    public function add($id = null)
    {
        $rules = [
            'name'     => 'required',
            'contact'  => 'required|numeric',
            'address'  => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respond(['message' => $this->validator->getErrors(), 'success' => false]);
        }

        $contact = $this->request->getPost('contact');

        $userModel = new User();

        // Check if contact already exists, including soft deleted records
        if ($id) {
            $existingEmployee = $userModel
                ->where('contact', $contact)
                ->where('id !=', $id)
                ->orWhere('contact', $contact) // Check also if the contact exists in soft deleted records
                ->countAllResults();
        } else {
            $existingEmployee = $userModel
                ->where('contact', $contact)
                ->orWhere('contact', $contact) // Check also if the contact exists in soft deleted records
                ->countAllResults();
        }

        if ($existingEmployee != 0) {
            return $this->respond(['message' => 'Contact already exists', 'success' => false]);
        }

        $userData = [
            'name'     => $this->request->getPost('name'),
            'contact'  => $contact,
            'address'  => $this->request->getPost('address'),
            'password' => md5($this->request->getPost('password')),
            'user_type' => 'employee' // Assuming default user type is employee
        ];

        if ($id) {
            $userModel->update($id, $userData);
        } else {
            $userModel->insert($userData);
        }

        return $this->respond(['success' => true, 'message' => 'Employee added successfully']);
    }


    public function delete($id = null)
    {
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
