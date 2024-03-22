<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerServicesModel extends Model
{
    protected $table = 'customer_services';
    protected $primaryKey = 'id';

    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['customer_id', 'service_master_id', 'purchase_date', 'expiry_date', 'user_id'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;
}
