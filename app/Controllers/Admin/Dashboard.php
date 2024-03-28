<?php

namespace App\Controllers\Admin;

use App\Controllers\Employees;
use App\Models\CustomerServicesModel;
use App\Models\LeadFollowupModel;
use CodeIgniter\RESTful\ResourceController;

class Dashboard extends ResourceController
{
    public function index()
    {
        $userType = $this->request->user->user_type;
        $userId = $this->request->user->id;
        $db = db_connect();
        $data['employees'] = $db->table('users')->countAllResults();

        $customer = $db->table('customers');
        if ($userType == 'executive')
            $customer->where('user_id', $userId);
        $data['customers'] = $customer->countAllResults();

        $leads = $db->table('leads');
        if ($userType == 'executive')
            $leads->where('user_id', $userId);
        $data['leads'] = $leads->countAllResults();

        $pendingService = $db->table('service_item as si');
        $pendingService->join('customer_services as cs', 'cs.id = si.customer_service_id');
        $pendingService->join('customers as c', 'c.id = cs.customer_id');
        $pendingService->where('si.servicing_date IS NULL');
        if ($userType == 'executive')
            $pendingService->where('c.user_id', $userId);
        $data['pending_services'] = $pendingService->countAllResults();

        $data['upcoming_services'] = $this->services($userId, $userType);
        $data['leads'] = $this->lead($userId, $userType);

        return $this->respond(['success' => true, 'data' => $data]);
    }

    private function services($userId, $userType)
    {
        $db = db_connect();
        $currentDate = date('Y-m-d'); // Get the current date
        $filter = $this->request->getPost('filter') ?? null;
        $limit = $this->request->getPost('limit') ?? null;

        // Start building the query
        $query = $db->table('service_item as si');
        $query->select('si.id, cs.id as service_id,si.date, si.comment, c.shop_name, c.owner_name, c.contact_number, c.whatsapp_number, c.address,si.servicing_date,si.remark,
             (DATEDIFF(si.date, "' . $currentDate . '") > 0) AS is_upcoming,
             (DATEDIFF(si.date, "' . $currentDate . '") = 0) AS is_pending,
             (DATEDIFF(si.date, "' . $currentDate . '") < 0) AS is_due,
             DATEDIFF(si.date, "' . $currentDate . '") AS days_remaining');
        $query->join('customer_services as cs', 'cs.id = si.customer_service_id', 'left');
        $query->join('customers as c', 'c.id = cs.customer_id', 'left');
        $query->where('si.servicing_date IS NULL');
        if ($userType == "executive")
            $query->where('c.user_id', $userId);
        $query = $query->orderBy('si.date', 'ASC');;

        if ($filter) {
            switch ($filter) {
                case 'due':
                    $query = $query->having('is_due', 1);
                    break;
                case 'pending':
                    $query = $query->having('is_pending', 1);
                    break;
                case 'upcoming':
                    $query = $query->having('is_upcoming', 1);
                    break;
                case 'completed':
                    $query = $query->where('servicing_date IS NOT NULL');
                    break;
                default:
                    return $this->respond(['success' => false, 'message' => 'Invalid filter value.']);
            }
        }


        $query->limit(10);
        // Execute the query and get the result
        $query = $query->get()->getResultArray();

        // Calculate the status and days remaining for each service
        foreach ($query as &$service) {
            if ($service['is_upcoming']) {
                $service['status'] = 'Upcoming';
            } elseif ($service['is_pending']) {
                $service['status'] = 'Pending';
            } elseif ($service['is_due']) {
                $service['status'] = 'Due';
            }
            // Convert days_remaining to a positive number if it's negative (due)
            $service['days_remaining'] = $service['days_remaining'];
        }

        return $query;
    }

    public function lead($userId, $userType)
    {
        $followUpModel = new LeadFollowupModel();
        $followUp = $followUpModel;
        $followUp->select('lead_followups.*,u.name as employee_name');
        $followUp->join('users as u', 'u.id = lead_followups.employee_id', 'left');
        $followUp->where('is_completed', null);
        if ($userType == "executive")
            $followUp->where('employee_id', $userId);
        $followUp->orderBy('id', 'desc');
        $followUp->limit(10);
        $followUp = $followUp->findAll();
        return $followUp;
    }
}
