<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;
use App\Models\AdvanceModel;
use App\Models\PartyModel;
use App\Models\CompanyModel;
use App\Models\AdvanceAttachmentModel;
use App\Libraries\ManagesCompanies;
use CodeIgniter\API\ResponseTrait;

class LoansController extends BaseController
{
    use ResponseTrait;
    protected $advanceModel;
    protected $companyHelper;

    public function __construct()
    {
        $this->advanceModel  = new AdvanceModel();
        $this->companyHelper = new ManagesCompanies();
    }

    public function index()
    {
        $request   = service('request');
        $activeTab = $request->getGet('tab') ?? 'payable';
        $status    = $request->getGet('status');
        $companyId = $request->getGet('company_id');
        $search    = trim((string) $request->getGet('search'));

        $builder = $this->advanceModel;
        if ($activeTab === 'receivable') {
            $builder = $builder->where('transaction_type', 'receivable_advance')->where('direction', 'IN');
        } else {
            $builder = $builder->where('transaction_type', 'recoverable_advance')->where('direction', 'OUT');
        }
        if ($status && $status !== 'all') $builder = $builder->where('status', $status);
        if ($companyId && $companyId !== 'all') $builder = $builder->where('company_id', $companyId);
        if ($search !== '') {
            $builder = $builder->groupStart()
                ->like('reference_number', $search)
                ->orLike('purpose', $search)
                ->groupEnd();
        }

        $advances = $builder->orderBy('transaction_date', 'desc')->paginate(20);
        $pager    = $this->advanceModel->pager;

        $stats = $this->getAdvanceStats();
        $partyModel   = new PartyModel();
        $companyModel = new CompanyModel();
        $parties = $partyModel->orderBy('name')->findAll();
        $companies = $companyModel->orderBy('name')->findAll();
        $partyMap = [];
        foreach ($parties as $party) {
            $partyMap[$party['id']] = $party;
        }
        $companyMap = [];
        foreach ($companies as $company) {
            $companyMap[$company['id']] = $company;
        }
        foreach ($advances as &$advance) {
            $advance['party_name'] = $partyMap[$advance['party_id']]['name'] ?? 'N/A';
            $advance['company_name'] = $companyMap[$advance['company_id']]['name'] ?? 'N/A';
        }
        unset($advance);

        return view('Manager/loans/index', [
            'advances'  => $advances,
            'pager'     => $pager,
            'stats'     => $stats,
            'parties'   => $parties,
            'companies' => $companies,
            'activeTab' => $activeTab,
            'filters'   => [
                'status' => $status ?? 'all',
                'company_id' => $companyId ?? 'all',
                'search' => $search,
            ],
        ]);
    }

    public function store()
    {
        $rules = [
            'advance_type' => 'required|in_list[payable,receivable]',
            'amount'       => 'required|numeric',
            'transaction_date' => 'required|valid_date',
            'purpose'      => 'required|max_length[500]',
            'company_id'   => 'required',
        ];
        if (!$this->validate($rules)) return $this->failValidationErrors($this->validator->getErrors());

        $db = \Config\Database::connect();
        $db->transStart();
        try {
            $type = $this->request->getPost('advance_type');
            $transType = $type === 'receivable' ? 'receivable_advance' : 'recoverable_advance';
            $direction = $type === 'receivable' ? 'IN' : 'OUT';
            $amount    = $this->request->getPost('amount');

            $this->advanceModel->insert([
                'transaction_type'       => $transType,
                'direction'              => $direction,
                'party_id'               => $this->request->getPost('party_id'),
                'party_type'             => $this->request->getPost('party_type') ?? 'other',
                'reference_number'       => $this->request->getPost('reference_number'),
                'amount'                 => $amount,
                'recovered_amount'       => 0,
                'outstanding_amount'     => $amount,
                'transaction_date'       => $this->request->getPost('transaction_date'),
                'expected_recovery_date' => $this->request->getPost('expected_date'),
                'status'                 => 'outstanding',
                'purpose'                => $this->request->getPost('purpose'),
                'comments'               => $this->request->getPost('comments'),
                'created_by'             => session()->get('user_id'),
                'company_id'             => $this->request->getPost('company_id'),
            ]);
            $advanceId = $this->advanceModel->getInsertID();

            $files = $this->request->getFiles();
            if (isset($files['attachments'])) {
                $attachModel = new AdvanceAttachmentModel();
                foreach ($files['attachments'] as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move(FCPATH . 'uploads/advances', $newName);
                        $attachModel->insert([
                            'advance_id' => $advanceId,
                            'file_name'  => $file->getClientName(),
                            'file_path'  => 'uploads/advances/' . $newName,
                            'file_type'  => $file->getClientExtension(),
                            'file_size'  => $this->companyHelper->formatBytes($file->getSize()),
                            'attachment_type' => 'loan_agreement',
                        ]);
                    }
                }
            }

            $db->transComplete();
            return $this->respondCreated(['success' => true, 'message' => 'Advance recorded successfully.']);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail('Error: ' . $e->getMessage());
        }
    }

    public function show($id = null)
    {
        $advance = $this->advanceModel->find($id);
        if (!$advance) return $this->failNotFound('Advance not found');
        $attachModel = new AdvanceAttachmentModel();
        $advance['attachments'] = $attachModel->where('advance_id', $id)->findAll();
        $recoveries = $this->advanceModel->where('linked_advance_id', $id)->findAll();
        $advance['recoveries'] = $recoveries;
        return $this->respond(['success' => true, 'data' => $advance]);
    }

    public function update($id = null)
    {
        $advance = $this->advanceModel->find($id);
        if (!$advance) return $this->failNotFound('Advance not found');

        $amount = $this->request->getPost('amount');
        if ($amount === null || $amount === '') {
            $amount = $advance['amount'];
        }
        $amount = (float) $amount;
        $recovered = (float) ($advance['recovered_amount'] ?? 0);
        $outstanding = max(0, $amount - $recovered);

        $this->advanceModel->update($id, [
            'party_id'                => $this->request->getPost('party_id') ?: $advance['party_id'],
            'party_type'              => $this->request->getPost('party_type') ?: $advance['party_type'],
            'company_id'              => $this->request->getPost('company_id') ?: $advance['company_id'],
            'amount'                  => $amount,
            'outstanding_amount'      => $outstanding,
            'transaction_date'        => $this->request->getPost('transaction_date') ?: $advance['transaction_date'],
            'reference_number'       => $this->request->getPost('reference_number'),
            'expected_recovery_date' => $this->request->getPost('expected_date') ?: $this->request->getPost('expected_recovery_date'),
            'purpose'                => $this->request->getPost('purpose') ?: $advance['purpose'],
            'comments'               => $this->request->getPost('comments'),
            'status'                 => $this->request->getPost('status') ?: ($outstanding > 0 ? $advance['status'] : 'recovered'),
        ]);
        return $this->respond(['success' => true, 'message' => 'Advance updated.']);
    }

    public function destroy($id = null)
    {
        $advance = $this->advanceModel->find($id);
        if (!$advance) return $this->failNotFound('Advance not found');
        $recoveries = $this->advanceModel->where('linked_advance_id', $id)->countAllResults();
        if ($recoveries > 0) return $this->fail('Cannot delete advance with recoveries.');
        $this->advanceModel->delete($id);
        return $this->respondDeleted(['success' => true, 'message' => 'Advance deleted.']);
    }

    public function storeRecovery($id = null)
    {
        $advance = $this->advanceModel->find($id);
        if (!$advance) return $this->failNotFound('Advance not found');

        $recoveryAmount = $this->request->getPost('recovery_amount');
        $newRecovered   = $advance['recovered_amount'] + $recoveryAmount;
        $newOutstanding = $advance['outstanding_amount'] - $recoveryAmount;
        $newStatus      = $newOutstanding <= 0 ? 'recovered' : ($newRecovered > 0 ? 'partially_recovered' : 'outstanding');

        $this->advanceModel->update($id, [
            'recovered_amount'   => $newRecovered,
            'outstanding_amount' => max(0, $newOutstanding),
            'status'             => $newStatus,
        ]);

        $recoveryType = $advance['transaction_type'] === 'receivable_advance' ? 'receivable_recovery' : 'advance_recovery';
        $recoveryDir  = $advance['transaction_type'] === 'receivable_advance' ? 'OUT' : 'IN';

        $this->advanceModel->insert([
            'transaction_type' => $recoveryType,
            'direction'        => $recoveryDir,
            'party_id'         => $advance['party_id'],
            'party_type'       => $advance['party_type'],
            'reference_number' => 'REC-' . ($advance['reference_number'] ?? $advance['id']),
            'amount'           => $recoveryAmount,
            'recovered_amount' => $recoveryAmount,
            'outstanding_amount' => 0,
            'transaction_date' => $this->request->getPost('recovery_date'),
            'status'           => 'recovered',
            'purpose'          => 'Recovery of advance #' . $advance['id'],
            'comments'         => $this->request->getPost('comments'),
            'created_by'       => session()->get('user_id'),
            'company_id'       => $advance['company_id'],
            'linked_advance_id' => $advance['id'],
        ]);

        return $this->respond(['success' => true, 'message' => 'Recovery recorded.']);
    }

    public function getStats()
    {
        return $this->respond(['success' => true, 'data' => $this->getAdvanceStats()]);
    }

    private function getAdvanceStats()
    {
        $m = new AdvanceModel();
        return [
            'total_payable_issued'      => $m->where('transaction_type', 'recoverable_advance')->where('direction', 'OUT')->selectSum('amount')->get()->getRow()->amount ?? 0,
            'total_payable_outstanding' => (new AdvanceModel())->where('transaction_type', 'recoverable_advance')->where('direction', 'OUT')->selectSum('outstanding_amount')->get()->getRow()->outstanding_amount ?? 0,
            'total_receivable_issued'   => (new AdvanceModel())->where('transaction_type', 'receivable_advance')->where('direction', 'IN')->selectSum('amount')->get()->getRow()->amount ?? 0,
            'total_receivable_outstanding' => (new AdvanceModel())->where('transaction_type', 'receivable_advance')->where('direction', 'IN')->selectSum('outstanding_amount')->get()->getRow()->outstanding_amount ?? 0,
            'overdue_count'  => (new AdvanceModel())->where('status', 'overdue')->countAllResults(),
            'overdue_amount' => (new AdvanceModel())->where('status', 'overdue')->selectSum('outstanding_amount')->get()->getRow()->outstanding_amount ?? 0,
        ];
    }
}
