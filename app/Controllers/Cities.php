<?php

namespace App\Controllers;

use App\Models\City;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\Response;

class Cities extends ResourceController
{
    public function getAll()
    {
        $cityModel = new City();
        $cities = $cityModel->findAll();

        return $this->respond(['success' => true, 'message' => 'Cities retrieved successfully', 'data' => $cities], Response::HTTP_OK);
    }

    public function delete($id = null)
    {
        if ($id === null) {
            return $this->fail(['success' => false, 'message' => 'Invalid city ID', 'data' => null], Response::HTTP_BAD_REQUEST);
        }

        $cityModel = new City();
        $city = $cityModel->find($id);

        if ($city === null) {
            return $this->fail(['success' => false, 'message' => 'City not found', 'data' => null], Response::HTTP_NOT_FOUND);
        }

        $cityModel->delete($id);

        return $this->respondDeleted(['success' => true, 'message' => 'City deleted successfully', 'data' => ['id' => $id]]);
    }

    public function add()
    {
        $cityModel = new City();

        $data = $this->request->getPost();

        if ($data['id'] != '') {
            if ($cityModel->update($data['id'], $data)) {
                return $this->respond(['success' => true, 'message' => 'City Updated successfully', 'data' => null]);
            } else {
                return $this->fail(['success' => false, 'message' => $cityModel->errors(), 'data' => null], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($cityModel->insert($data)) {
            return $this->respond(['success' => true, 'message' => 'City added successfully', 'data' => null]);
        } else {
            return $this->fail(['success' => false, 'message' => $cityModel->errors(), 'data' => null], Response::HTTP_BAD_REQUEST);
        }
    }

}
