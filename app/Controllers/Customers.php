<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\LeadModel;
use CodeIgniter\RESTful\ResourceController;

class Customers extends ResourceController
{
    public function get()
    {
        $response = ['success' => false, 'message' => 'No Customers Found'];
        $customerModel = new CustomerModel();
        $customers = $customerModel;
        $customers->select('customers.*,(select count(*) from customer_services where customer_id = customers.id and expiry_date >="' . date('Y-m-d') . '") as active_services');
        if ($this->request->user->user_type == "executive")
            $customers->where('user_id', $this->request->user->id);
        $customers = $customers->findAll();
        if (empty($customers))
            return $this->respond($response);
        return $this->respond(['success' => true, 'data' => $customers, 'message' => '']);
    }

    public function add($id = null)
    {
        $rules = [
            'shop_name'        => 'required',
            'owner_name'       => 'required',
            'contact_number'   => 'required|numeric',
            'whatsapp_number' => 'required|numeric',
            'address'          => 'required',
        ];

        if ($id == null) {
            $rules['shutter_image'] = 'uploaded[shutter_image]|max_size[shutter_image,1024]|ext_in[shutter_image,jpg,jpeg,png]';
        }


        if (!$this->validate($rules)) {
            return $this->respond(['message' => $this->validator->getErrors(), 'success' => false]);
        }

        $customerModel = new CustomerModel();

        $customerData = [
            'shop_name'        => $this->request->getVar('shop_name'),
            'owner_name'       => $this->request->getVar('owner_name'),
            'contact_number'   => $this->request->getVar('contact_number'),
            'whatsapp_number' => $this->request->getVar('whatsapp_number'),
            'address'          => $this->request->getVar('address'),
            'user_id' => $this->request->user->id,
        ];

        if ($id) {
            $customerModel->update($id, $customerData);
            $customerId = $id;
        } else {
            $customerId = $customerModel->insert($customerData);
        }

        // Handle file upload after the entry is made
        if ($file = $this->request->getFile('shutter_image')) {
            if ($file->isValid() && !$file->hasMoved()) {
                echo $newName = 'customer_' . $customerId . '.' . $file->getClientExtension();
                if ($id) {
                    $old_img = $customerModel->find($customerId);
                    if (file_exists($old_img['image'])) {
                        unlink($old_img['image']);
                    }
                }
                $file->move('assets/uploads', $newName);
                $customerData['image'] = 'assets/uploads/' . $newName;
                // Compress image if size is more than 1 MB
                $compressedImagePath = $this->compressImage('assets/uploads/' . $newName, 500 * 1024);
                if ($compressedImagePath) {
                    $customerData['image'] = $compressedImagePath;
                }

                // Update the database with the new image path
                $customerModel->update($customerId, ['image' => $customerData['image']]);
            }
        }

        $profile = $customerModel->find($customerId);
        return $this->respond(['success' => true, 'message' => 'Customer ' . ($id ? 'updated' : 'added') . ' successfully', 'data' => $profile]);
    }

    private function compressImage($imagePath, $maxSize)
    {
        $imageInfo = getimagesize($imagePath);
        $mime = $imageInfo['mime'];

        if ($mime == 'image/jpeg') {
            $image = imagecreatefromjpeg($imagePath);
        } elseif ($mime == 'image/png') {
            $image = imagecreatefrompng($imagePath);
        }

        $fileSize = filesize($imagePath);

        if ($fileSize > $maxSize) {
            $quality = 100; // Start with 100% quality
            while ($fileSize > $maxSize && $quality > 0) {
                $quality -= 10; // Reduce quality by 10%
                if ($mime == 'image/jpeg') {
                    imagejpeg($image, $imagePath, $quality);
                } elseif ($mime == 'image/png') {
                    imagepng($image, $imagePath, 9 - ($quality / 10));
                }
                $fileSize = filesize($imagePath);
            }
        }

        return $imagePath;
    }


    public function delete($id = null)
    {
        if ($id === null) {
            return $this->failValidationError('Customer ID is required for deletion');
        }

        $customerModel = new CustomerModel();
        $customer = $customerModel->find($id);

        if (!$customer) {
            return $this->respond(['success' => false, 'message' => 'Customer not found']);
        }

        $customerModel->delete($id);

        return $this->respondDeleted(['success' => true, 'message' => 'Customer deleted successfully']);
    }

    public function undoDelete($id = null)
    {
        if ($id === null) {
            return $this->respond(['message' => 'Customer ID is required', 'success' => false]);
        }

        $customerModel = new CustomerModel();
        $customer = $customerModel->onlyDeleted()->find($id);

        if (!$customer) {
            return $this->respond(['message' => 'Deleted customer not found', 'success' => false]);
        }

        // Restore the deleted customer
        $db = db_connect();
        $db->table('customers')->where('id', $id)->update(["deleted_at" => NULL]);

        return $this->respond(['success' => true, 'message' => 'Customer undeleted successfully']);
    }

    public function convertLeadToCustomer($leadId = null)
    {
        if ($leadId === null) {
            return $this->respond(['message' => 'Lead ID is required', 'success' => false]);
        }

        $leadModel = new LeadModel();
        $lead = $leadModel->find($leadId);

        if (!$lead) {
            return $this->respond(['message' => 'Lead not found', 'success' => false]);
        }

        // Prepare data for customer creation
        $customerData = [
            'shop_name'        => $lead['shop_name'],
            'owner_name'       => $lead['owner_name'],
            'contact_number'   => $lead['contact_number'],
            'whatsapp_number'  => $lead['whatsapp_number'],
            'address'          => $lead['address'],
            'user_id'          => $lead['user_id']
        ];

        // Create a new customer record
        $customerModel = new CustomerModel();
        $customerId = $customerModel->insert($customerData);

        if (!$customerId) {
            return $this->respond(['message' => 'Failed to convert lead to customer', 'success' => false]);
        }

        // Delete the lead
        $leadModel->delete($leadId);

        return $this->respond(['success' => true, 'message' => 'Lead converted to customer successfully', 'customer_id' => $customerId]);
    }
}
