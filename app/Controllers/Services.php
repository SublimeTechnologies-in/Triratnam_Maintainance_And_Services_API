<?php

namespace App\Controllers;

use App\Models\CustomerServicesModel;
use App\Models\ServiceItemModel;
use App\Models\ServiceMasterModel;
use CodeIgniter\RESTful\ResourceController;

class Services extends ResourceController
{
    public function add($customer_id, $service_id, $id = null)
    {
        $customerServiceId = 0;

        $customerServicesModel = new CustomerServicesModel();
        $serviceMasterModel = new ServiceMasterModel(); // Ensure this model exists and is correctly set up

        $serviceMaster = $serviceMasterModel->find($service_id);
        if (!$serviceMaster) {
            return $this->respond(['message' => 'Service Master not found', 'success' => false]);
        }

        $purchaseDate = date('Y-m-d');
        $expiryDate = date('Y-m-d', strtotime($purchaseDate . ' +' . $serviceMaster['duration'] . ' days'));

        $customerServiceData = [
            'customer_id' => $customer_id,
            'service_master_id' => $service_id,
            'purchase_date' => $purchaseDate,
            'expiry_date' => $expiryDate,
            // 'user_id' => $this->request->user->id,
        ];

        if ($id) {
            $customerServiceId = $id;
            $customerServicesModel->update($id, $customerServiceData);
        } else {
            $customerServiceId = $customerServicesModel->insert($customerServiceData);
        }

        // Calculate and insert service items
        $serviceItemModel = new ServiceItemModel(); // Ensure this model exists and is correctly set up
        $interval = $serviceMaster['duration'] / $serviceMaster['number_of_services'];
        $startDate = strtotime($purchaseDate);
        for ($i = 0; $i < $serviceMaster['number_of_services']; $i++) {
            $serviceItemDate = date('Y-m-d', $startDate + ($i * $interval * 24 * 60 * 60));
            $serviceItemData = [
                'customer_service_id' => $customerServiceId,
                'date' => $serviceItemDate,
                'comment' => 'Service item for customer service ' . $customerServiceId,
            ];
            $serviceItemModel->insert($serviceItemData);
        }

        return $this->respond(['success' => true, 'message' => 'Customer Service ' . ($id ? 'updated' : 'added') . ' successfully', 'data' => $customerServiceData]);
    }

    public function getServicesByCustomerId($customer_id)
    {
        $customerServicesModel = new CustomerServicesModel();

        // Retrieve all services for the given customer ID
        $customerServices = $customerServicesModel
            ->select('customer_services.expiry_date,customer_services.purchase_date,si.*')
            ->join('service_item as si', 'si.customer_service_id = customer_services.id', 'left')
            ->where('customer_id', $customer_id)
            ->where('expiry_date >="' . date('Y-m-d') . '"')
            ->findAll();

        if (empty($customerServices)) {
            return $this->respond(['message' => 'No services found for this customer', 'success' => false]);
        }

        $data = [];
        foreach ($customerServices as $value) {
            $data[$value['customer_service_id']][] = $value;
        }

        $finalData = [];
        foreach ($data as $value) {
            $finalData[] = $value;
        }


        return $this->respond(['success' => true, 'data' => $finalData]);
    }

    function getAllServices()
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
        if ($this->request->user->user_type == "executive")
            $query->where('c.user_id', $this->request->user->id);
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

        if ($limit)
            $query->limit($limit);
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

        return $this->respond(['success' => true, 'data' => $query]);
    }

    function markAsServiceComplete()
    {
        $db = db_connect();
        $siId = $this->request->getPost('service_id');
        $data['remark'] = $this->request->getPost('remark');
        $undo = $this->request->getPost('undo') ?? null;
        $data['servicing_date'] = date('Y-m-d'); // Get the current date
        if ($undo != null) $data['servicing_date'] = null;
        $db->table('service_item')->update($data, ['id' => $siId]);
        return $this->respond(['success' => true, 'message' => ($undo != null) ? 'Service Unmarked As Completed' : 'Service Mark As Completed']);
    }

    function deleteService($id)
    {
        $customerServicesModel = new CustomerServicesModel();
        $customerServicesModel->delete($id);
        return $this->respond(['success' => true, 'message' => 'Delete successful']);
    }
}
