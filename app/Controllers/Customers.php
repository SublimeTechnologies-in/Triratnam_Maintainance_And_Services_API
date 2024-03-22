<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\LeadModel;
use CodeIgniter\RESTful\ResourceController;

class Customers extends ResourceController
{
    protected $modelName = 'App\Models\CustomerModel';

    public function get()
    {
        $response = ['success' => false, 'message' => 'No Customers Found'];
        $customerModel = new CustomerModel();
        $customers = $customerModel
            ->findAll();
        if (empty($customers))
            return $this->respond($response);
        return $this->respond(['success' => true, 'data' => $customers, 'message' => '']);
    }

    public function add($id = null)
    {
        $customerId = 0;
        $rules = [
            'shop_name'        => 'required',
            'owner_name'       => 'required',
            'contact_number'   => 'required|numeric',
            'whatsapp_number'  => 'required|numeric',
            'address'          => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respond(['message' => $this->validator->getErrors(), 'success' => false]);
        }

        $customerModel = new CustomerModel();

        $customerData = [
            'shop_name'        => $this->request->getVar('shop_name'),
            'owner_name'       => $this->request->getVar('owner_name'),
            'contact_number'   => $this->request->getVar('contact_number'),
            'whatsapp_number'  => $this->request->getVar('whatsapp_number'),
            'address'          => $this->request->getVar('address'),
            'user_id' => $this->request->user->id,
        ];

        if ($id) {
            $customerId = $id;
            $customerModel->update($id, $customerData);
        } else {
            $customerId = $customerModel->insert($customerData);
        }

        $profile = $customerModel
            ->find($customerId);
        return $this->respond(['success' => true, 'message' => 'Customer ' . ($id ? 'updated' : 'added') . ' successfully', 'data' => $profile]);
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
