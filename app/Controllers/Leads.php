<?php

namespace App\Controllers;

use App\Models\LeadModel;
use CodeIgniter\RESTful\ResourceController;

class Leads extends ResourceController
{
    protected $modelName = 'App\Models\LeadModel';

    public function get()
    {
        $response = ['success' => false, 'message' => 'No Leads Found'];
        $leadModel = new LeadModel();
        $leads = $leadModel->findAll();
        if (empty($leads))
            return $this->respond($response);
        return $this->respond(['success' => true, 'data' => $leads, 'message' => '']);
    }

    public function add($id = null)
    {
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

        $leadModel = new LeadModel();

        $leadData = [
            'shop_name'        => $this->request->getVar('shop_name'),
            'owner_name'       => $this->request->getVar('owner_name'),
            'contact_number'   => $this->request->getVar('contact_number'),
            'whatsapp_number'  => $this->request->getVar('whatsapp_number'),
            'address'          => $this->request->getVar('address')
        ];

        if ($id) {
            $leadModel->update($id, $leadData);
        } else {
            $leadModel->insert($leadData);
        }

        return $this->respond(['success' => true, 'message' => 'Lead ' . ($id ? 'updated' : 'added') . ' successfully']);
    }

    public function delete($id = null)
    {
        if ($id === null) {
            return $this->failValidationError('Lead ID is required for deletion');
        }

        $leadModel = new LeadModel();
        $lead = $leadModel->find($id);

        if (!$lead) {
            return $this->respond(['success' => false, 'message' => 'Lead not found']);
        }

        $leadModel->delete($id);

        return $this->respondDeleted(['success' => true, 'message' => 'Lead deleted successfully']);
    }

    public function undoDelete($id = null)
    {
        if ($id === null) {
            return $this->respond(['message' => 'Lead ID is required', 'success' => false]);
        }

        $leadModel = new LeadModel();
        $lead = $leadModel->onlyDeleted()->find($id);

        if (!$lead) {
            return $this->respond(['message' => 'Deleted lead not found', 'success' => false]);
        }

        // Restore the deleted lead
        $db = db_connect();
        $db->table('leads')->where('id', $id)->update(["deleted_at" => NULL]);

        return $this->respond(['success' => true, 'message' => 'Lead undeleted successfully']);
    }
}
