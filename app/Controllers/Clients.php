<?php

namespace App\Controllers;

use App\Models\Client;
use App\Models\Credential;
use App\Models\Transaction;
use CodeIgniter\RESTful\ResourceController;



class Clients extends ResourceController
{
    function dashboard()
    {
        return $this->respond(['success' => true, 'message' => 'data received', 'data' => []]);
    }

    function getProfile()
    {
        $id = $this->request->user->id;
        $clientModel = new Client();
        $credentialModel = new Credential();
        $transactionModel = new Transaction();
        $data['profile'] = $clientModel->select('clients.*,(select CONCAT(name,"-",state) from cities where id = clients.city_id limit 1) as city')->where('credential_id', $id)->first();
        $data['status'] = $credentialModel->select('id,is_active,name,username')->where('id', $id)->first();
        $data['coins'] = $transactionModel->where('user_id', $data['status']['id'])->orderBy('id', 'desc')->limit(10)->findAll();
        if ($data) {
            return $this->respond(['success' => true, 'data' => $data]);
        }
        return $this->respond(['success' => false, 'message' => "No Data Available"]);
    }
    
    public function add()
    {
        $clientModel = new Client();
        $credentialModel = new Credential();
        $ref_id = $credentialId = $this->request->user->id;
        $db = db_connect();
        $rules = [
            'name' => 'required|string|max_length[255]',
            'type' => 'required|string|max_length[255]',
            'contact' => 'required|max_length[10]',
            'alternate_contact' => 'permit_empty|valid_phone_number',
            'email' => 'required|valid_email|max_length[255]',
            'address' => 'required|string|max_length[255]',
            'city_id' => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return $this->respond(['success' => false, 'message' => $this->validator->getErrors()]);
        }
        $idProof = $this->request->getFile('id_proof_link');
        $profile_photo = $this->request->getFile('profile_photo');

        if ($idProof == null || $idProof->getSize() < 0) {
            return $this->respond(['success' => false, 'message' => "idProof Required"]);
        }
        if ($profile_photo == null || $profile_photo->getSize() < 0) {
            return $this->respond(['success' => false, 'message' => "profile photo Required"]);
        }

        $db->transBegin(); // Start transaction

        try {
            $clientData = [
                'name' => $this->request->getPost('name'),
                'overview' => $this->request->getPost('overview'),
                'type' => $this->request->getPost('type'),
                'contact' => $this->request->getPost('contact'),
                'alternate_contact' => $this->request->getPost('alternate_contact'),
                'email' => $this->request->getPost('email'),
                'website' => $this->request->getPost('website'),
                'address' => $this->request->getPost('address'),
                'credential_id' => $credentialId, // Assuming the credential ID is stored in the session
                'ref_id' => $ref_id,
                'city_id' => $this->request->getPost('city_id'),
                // Add any other fields you want to save
            ];

            $clientId = $clientModel->insert($clientData);

            if (!$clientId) {
                return $this->respond(['success' => false, 'message' => "Failed to save client data"]);
            }

            // Upload files
            $idProof = $this->request->getFile('id_proof_link');
            $profile_photo = $this->request->getFile('profile_photo');
            $filenamePrefix = $clientId . '_Client_' . strtolower(str_replace(' ', '', $this->request->getPost('name')));

            $idProofName = $filenamePrefix . '_idProof.' . $idProof->getClientExtension();
            $profilePhotoName = $filenamePrefix . '_profile.' . $profile_photo->getClientExtension();

            $idProof->move('assets/client', $idProofName);
            $profile_photo->move('assets/client', $profilePhotoName);

            // Update executive data with file information
            $updateData = [
                'id_proof_link' => $idProofName,
                'profile_photo' => $profilePhotoName,
                // Add any other file-related fields if needed
            ];

            $clientModel->update($clientId, $updateData);
            if ($db->transStart === false) {
                $db->transRollback();
                return $this->respond(['success' => false, 'message' => "Something went wrong please try again!"]);
            }
            $db->transCommit(); // Commit the transaction
            return $this->respond(['success' => true, 'message' => "Client Updated Successfully"]);
        } catch (\Exception $e) {
            $db->transRollback(); // Rollback the transaction in case of exception
            return $this->respond(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateClient()
    {
        $id = $this->request->user->id;
        $clientModel = new Client();
        $credentialModel = new Credential();
        $db = db_connect();
        $rules = [
            'name' => 'required|string|max_length[255]',
            'type' => 'required|string|max_length[255]',
            'contact' => 'required|max_length[10]',
            'alternate_contact' => 'permit_empty|valid_phone_number',
            'email' => 'required|valid_email|max_length[255]',
            'address' => 'required|string|max_length[255]',
            'city_id' => 'required|integer',
        ];


        if (!$this->validate($rules)) {
            return $this->respond(['success' => false, 'message' => $this->validator->getErrors()]);
        }

        $db->transBegin(); // Start transaction

        try {

            $existingClientData = $clientModel->where('credential_id', $id)->first();

            if (!$existingClientData) {
                return $this->respond(['success' => false, 'message' => 'Client not found']);
            }

            $loginData = [
                'name' => $this->request->getPost('name'),
                'username' => $this->request->getPost('contact'),
                'is_active' => 1
            ];

            // Check if the new Contact Number Already Exist (excluding the current executive's username)
            if ($credentialModel->where('username', $loginData['username'])->where('id !=', $existingClientData['credential_id'])->countAllResults() > 0) {
                $db->transRollback(); // Rollback the transaction
                return $this->respond(['success' => false, 'message' => 'Contact Number Already Exist']);
            }

            $credentialId = $existingClientData['credential_id'];

            // Update login data
            $credentialModel->update($credentialId, $loginData);

            $clientData = [
                'name' => $this->request->getPost('name'),
                'overview' => $this->request->getPost('overview'),
                'type' => $this->request->getPost('type'),
                'contact' => $this->request->getPost('contact'),
                'alternate_contact' => $this->request->getPost('alternate_contact'),
                'email' => $this->request->getPost('email'),
                'website' => $this->request->getPost('website'),
                'address' => $this->request->getPost('address'),
                'credential_id' => $credentialId, // Assuming the credential ID is stored in the session
                'city_id' => $this->request->getPost('city_id'),
                // Add any other fields you want to save
            ];
            // Update executive data
            $clientModel->update($existingClientData['id'], $clientData);

            // Upload new files 
            $profile_photo = $this->request->getFile('profile_photo');
            $idProof = $this->request->getFile('id_proof_link');

            if ($profile_photo && $profile_photo->getSize() > 0) {
                // Delete old photo file
                $photoToDelete = 'assets/client/' . $existingClientData['profile_photo'];
                if (file_exists($photoToDelete)) {
                    unlink($photoToDelete);
                }

                // Upload new photo file
                $photoName = $this->uploadFile('profile_photo', $id, $clientData['name'], 'photo');
                $clientModel->update($existingClientData['id'], ['profile_photo' => $photoName]);
            }

            if ($idProof && $idProof->getSize() > 0) {
                // Delete old idProof file
                $idProofToDelete = 'assets/client/' . $existingClientData['id_proof_link'];
                if (file_exists($idProofToDelete)) {
                    unlink($idProofToDelete);
                }

                // Upload new idProof file
                $idProofName = $this->uploadFile('id_proof_link', $id, $clientData['name'], '_idProof');
                $clientModel->update($existingClientData['id'], ['id_proof_link' => $idProofName]);
            }

            if ($db->transStart === false) {
                $db->transRollback();
                return $this->respond(['success' => false, 'message' => "Something went wrong please try again!"]);
            }
            $db->transCommit(); // Commit the transaction
            return $this->respond(['success' => true, 'message' => "Client Updated Successfully"]);
        } catch (\Exception $e) {
            $db->transRollback(); // Rollback the transaction in case of exception
            return $this->respond(['success' => false, 'message' => "Client 294 Exception : Please try Again Or Contact To Developer"]);
        }
    }

    private function uploadFile($fieldName, $clientId, $clientName, $fileType)
    {
        $file = $this->request->getFile($fieldName);
        $filenamePrefix = $clientId . '_Client_' . strtolower(str_replace(' ', '', $clientName));
        $filename = $filenamePrefix . '_' . $fileType . '.' . $file->getClientExtension();
        $file->move('assets/client', $filename);
        return $filename;
    }
}
