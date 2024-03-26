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
}
