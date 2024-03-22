<?php

namespace App\Controllers;

use App\Models\LeadFollowupModel;
use CodeIgniter\RESTful\ResourceController;

class LeadFollowups extends ResourceController
{
    public function get($lead_id = null)
    {
        $followUpModel = new LeadFollowupModel();
        $followUp = $followUpModel->select('lead_followups.*,u.name as employee_name')->join('users as u', 'u.id = lead_followups.employee_id','left')->where('lead_id', $lead_id)->orderBy('id', 'desc')->findAll();
        if ($followUp) {
            return  $this->respond(["success" => true, 'data' => $followUp]);
        } else {
            return  $this->respond(["success" => false, 'message' => "No Data Found"]);
        }
    }

    public function add()
    {
        $lead_id = $this->request->getVar('lead_id');
        $employee_id = $this->request->user->id;
        $date = date('Y-m-d'); // Assuming current date
        $message = $this->request->getVar('message');

        // Validate inputs as needed

        $followUpModel = new LeadFollowupModel();

        $data = [
            'lead_id' => $lead_id,
            'employee_id' => $employee_id,
            'date' => $date,
            'message' => $message
        ];

        if ($followUpModel->insert($data)) {
            return $this->respond(["success" => true, 'message' => "Lead follow-up added successfully"]);
        } else {
            return $this->respond(["success" => false, 'message' => "Failed to add lead follow-up"]);
        }
    }

    public function delete($id = null)
    {
        $followUpModel = new LeadFollowupModel();
        $leadFollowup = $followUpModel->find($id);

        if ($leadFollowup) {
            if ($followUpModel->delete($id)) {
                return $this->respond(["success" => true, 'message' => "Lead follow-up deleted successfully"]);
            } else {
                return $this->respond(["success" => false, 'message' => "Failed to delete lead follow-up"]);
            }
        } else {
            return $this->respond(["success" => false, 'message' => "Lead follow-up not found"]);
        }
    }
}
