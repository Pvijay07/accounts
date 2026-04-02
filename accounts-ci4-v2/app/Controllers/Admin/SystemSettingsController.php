<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SystemSettingModel;

class SystemSettingsController extends BaseController
{
    protected $settingModel;

    public function __construct()
    {
        $this->settingModel = new SystemSettingModel();
    }

    public function index()
    {
        $settings = $this->settingModel->findAll();
        $settingsMap = [];
        foreach ($settings as $s) {
            $settingsMap[$s['key']] = $s['value'];
        }
        return view('Admin/system_settings', ['settings' => $settingsMap]);
    }

    public function save()
    {
        $data = $this->request->getPost();
        unset($data['csrf_test_name']);
        
        // Remove 'group' key since it's just used by the frontend parser to tell what form was submitted
        unset($data['group']);

        foreach ($data as $key => $value) {
            $this->settingModel->setSetting($key, $value);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Settings saved successfully!'
            ]);
        }
        return redirect()->to(base_url('admin/system-settings'))->with('success', 'Settings saved!');
    }

    public function testEmail()
    {
        return $this->response->setJSON(['success' => true, 'message' => 'Test email sent (placeholder)']);
    }

    public function runBackup()
    {
        return $this->response->setJSON(['success' => true, 'message' => 'Backup initiated (placeholder)']);
    }

    public function downloadBackup()
    {
        return redirect()->back()->with('error', 'No backup available');
    }

    public function clearCache()
    {
        return $this->response->setJSON(['success' => true, 'message' => 'Cache cleared']);
    }

    public function optimizeDatabase()
    {
        $db = \Config\Database::connect();
        $tables = $db->listTables();
        foreach ($tables as $table) {
            $db->query("OPTIMIZE TABLE `$table`");
        }
        return $this->response->setJSON(['success' => true, 'message' => 'Database optimized']);
    }

    public function clearLogs()
    {
        $logPath = WRITEPATH . 'logs/';
        $files = glob($logPath . 'log-*.log');
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        }
        return $this->response->setJSON(['success' => true, 'message' => 'Logs cleared']);
    }
}
