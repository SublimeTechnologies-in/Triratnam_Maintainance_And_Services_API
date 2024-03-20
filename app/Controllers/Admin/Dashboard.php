<?php

namespace App\Controllers\Admin;

use App\Models\Client;
use App\Models\Credential;
use App\Models\Executive;
use App\Models\Transaction;
use App\Models\User;
use CodeIgniter\RESTful\ResourceController;

class Dashboard extends ResourceController
{
    public function index()
    {
        $data = [];
        $executiveModel = new Executive();
        $clientModel = new Client();
        $userModel = new User();
        $transactionModel = new Transaction();
        $credentialModel = new Credential();
        $userData = $this->request->user;
        $userType = $userData->user_type; // admin/executive
        $userId = $userData->id;

        $data['executives'] = ($userType === 'executive') ? 1 : $executiveModel->countAllResults();
        $data['clients'] = ($userType === 'executive') ? $credentialModel->where('ref_id', $userId)->where('user_type', 'client')->countAllResults() : $credentialModel->countAllResults();
        $data['users'] = ($userType === 'executive') ? $credentialModel->where('ref_id', $userId)->where('user_type', 'user')->countAllResults() : $credentialModel->countAllResults();

        $data['thisMonthClients'] = ($userType === 'executive') ? $credentialModel->where('ref_id', $userId)->where('user_type', 'client')->where('MONTH(created_at) = ' . date('m'))->countAllResults() : $credentialModel->countAllResults();
        $data['thisMonthUsers'] = ($userType === 'executive') ? $credentialModel->where('ref_id', $userId)->where('user_type', 'user')->where('MONTH(created_at) = ' . date('m'))->countAllResults() : $credentialModel->countAllResults();

        // Using Query Builder for transactions
        $transactions = $transactionModel->select('transactions.*,c.name as name');
        $transactions->join('credentials as c', 'c.id = transactions.user_id');
        $transactions->where('coins < 0');
        if ($userType == 'executive')
            $transactions->where('user_id', $userId);
        $transactions->orderBy('id', 'desc');
        $transactions->limit(10);
        $transactions = $transactions->findAll();
        $data['transactions'] = $transactions;

        $data['coins'] = $transactionModel->select('total')->where('user_id', $userId)->orderBy('id', 'desc')->first();

        $data['graph']['executives'] = [];
        if ($userType == 'admin') {
            $executiveGraphData = $executiveModel->select('MONTH(executives.created_at) AS month, YEAR(executives.created_at) AS YEAR, COUNT(*) AS count');
            $executiveGraphData->join('credentials', 'executives.credential_id = credentials.id');
            $executiveGraphData->where('executives.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)');
            $executiveGraphData->groupBy('YEAR(executives.created_at), MONTH(executives.created_at)');
            $executiveGraphData->orderBy('YEAR ASC, MONTH ASC');
            $data['graph']['executives'] = $executiveGraphData->findAll();
        }


        $clientGraphData = $credentialModel->select('MONTH(credentials.created_at) AS month, YEAR(credentials.created_at) AS YEAR, COUNT(*) AS count');
        // $clientGraphData->join('credentials', 'clients.credential_id = credentials.id');
        if ($userType == 'executive')
            $clientGraphData->where('credentials.ref_id', $userId);
        $clientGraphData->where('credentials.user_type', 'client');
        $clientGraphData->where('credentials.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)');
        $clientGraphData->groupBy('YEAR(credentials.created_at), MONTH(credentials.created_at)');
        $clientGraphData->orderBy('YEAR ASC, MONTH ASC');
        $data['graph']['clients'] = $clientGraphData->findAll();


        $userGraphData = $credentialModel->select('MONTH(credentials.created_at) AS month, YEAR(credentials.created_at) AS YEAR, COUNT(*) AS count');
        if ($userType == "executive")
            $userGraphData->where('credentials.ref_id', $userId);
        $userGraphData->where('credentials.user_type', 'user');
        $userGraphData->where('credentials.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)');
        $userGraphData->groupBy('YEAR(credentials.created_at), MONTH(credentials.created_at)');
        $userGraphData->orderBy('YEAR ASC, MONTH ASC');
        $data['graph']['users'] = $userGraphData->findAll();

        return $this->respond(['success' => true, 'message' => 'received', 'data' => $data]);
    }
}
