<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;

class ActivityLogController extends BaseController
{
    protected $logModel;

    public function __construct()
    {
        $this->logModel = new ActivityLogModel();
    }

    public function index()
    {
        $logs = $this->logModel->orderBy('created_at', 'desc')->paginate(50);
        $pager = $this->logModel->pager;
        return view('Admin/activity_logs', ['logs' => $logs, 'pager' => $pager]);
    }

    public function show($id = null)
    {
        $log = $this->logModel->find($id);
        if (!$log) return $this->response->setJSON(['success' => false, 'message' => 'Not found']);
        return $this->response->setJSON(['success' => true, 'log' => $log]);
    }

    public function export()
    {
        $logs = $this->logModel->orderBy('created_at', 'desc')->findAll();
        $filename = 'activity_logs_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'User', 'Action', 'Details', 'IP', 'Date']);
        foreach ($logs as $log) {
            fputcsv($output, [$log['id'], $log['user_id'], $log['action'], $log['details'], $log['ip_address'], $log['created_at']]);
        }
        fclose($output);
        exit;
    }

    public function clear()
    {
        $this->logModel->truncate();
        return redirect()->to(base_url('admin/activity-logs'))->with('success', 'Activity logs cleared.');
    }
}
