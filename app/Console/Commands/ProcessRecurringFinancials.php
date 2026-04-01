<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessRecurringFinancials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financials:process-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate recurring invoices, income, and standard expenses based on frequency and due date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting recurring financials processing...');
        
        $this->processRecurringInvoices();
        $this->processStandardExpenses();
        $this->processRecurringIncomes();
        
        $this->info('Recurring financials processing completed.');
    }

    /**
     * Process recurring invoices.
     */
    private function processRecurringInvoices()
    {
        $this->info('Processing recurring invoices...');
        
        $today = Carbon::today();
        
        // Find invoices that are recurring
        $templates = Invoice::where('is_recurring', true)
            ->whereNotNull('frequency')
            ->get();

        foreach ($templates as $template) {
            try {
                if ($this->shouldGenerateForDate($template, $today)) {
                    $this->generateInvoice($template, $today);
                }
            } catch (\Exception $e) {
                Log::error("Error generating recurring invoice for template ID {$template->id}: " . $e->getMessage());
                $this->error("Error for Invoice {$template->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Process standard expenses.
     */
    private function processStandardExpenses()
    {
        $this->info('Processing standard expenses...');
        
        $today = Carbon::today();
        
        // Standard expenses are typically recurring templates
        $templates = Expense::where('source', 'standard')
            ->where('is_recurring', true)
            ->whereNotNull('frequency')
            ->get();

        foreach ($templates as $template) {
            try {
                if ($this->shouldGenerateForDate($template, $today)) {
                    $this->generateExpense($template, $today);
                }
            } catch (\Exception $e) {
                Log::error("Error generating standard expense for template ID {$template->id}: " . $e->getMessage());
                $this->error("Error for Expense {$template->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Process recurring incomes.
     */
    private function processRecurringIncomes()
    {
        $this->info('Processing recurring incomes...');
        
        $today = Carbon::today();
        
        // Templates are entries with a frequency and source='standard'
        $templates = Income::where('source', 'standard')
            ->whereNotNull('frequency')
            ->get();

        foreach ($templates as $template) {
            try {
                if ($this->shouldGenerateForDate($template, $today)) {
                    $this->generateIncome($template, $today);
                }
            } catch (\Exception $e) {
                Log::error("Error generating recurring income for template ID {$template->id}: " . $e->getMessage());
                $this->error("Error for Income {$template->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Determine if a new record should be generated today based on freq and due_day.
     */
    private function shouldGenerateForDate($template, Carbon $date)
    {
        $frequency = strtolower($template->frequency);
        $dueDay = $template->due_day;

        // If due_day is not set, we can't reliably automate
        if (!$dueDay) return false;

        switch ($frequency) {
            case 'monthly':
                // Check if today matches the due day
                if ($date->day == $dueDay) {
                    return !$this->alreadyGenerated($template, $date, 'month');
                }
                break;
            
            case 'quarterly':
                // Months: 1, 4, 7, 10 (Standard quarterly start months)
                if (in_array($date->month, [1, 4, 7, 10]) && $date->day == $dueDay) {
                    return !$this->alreadyGenerated($template, $date, 'quarter');
                }
                break;

            case 'yearly':
                // Assume generated once a year. If issue_date exists, use its month, else month 1.
                $targetMonth = 1;
                if ($template instanceof Invoice && $template->issue_date) {
                    $targetMonth = $template->issue_date->month;
                } elseif ($template instanceof Income && $template->income_date) {
                    $targetMonth = $template->income_date->month;
                }
                
                if ($date->month == $targetMonth && $date->day == $dueDay) {
                    return !$this->alreadyGenerated($template, $date, 'year');
                }
                break;

            case 'weekly':
                // 1-7 represents Mon-Sun
                if ($date->dayOfWeekIso == $dueDay) {
                    return !$this->alreadyGenerated($template, $date, 'week');
                }
                break;
        }

        return false;
    }

    /**
     * Check if a record already exists for the given period.
     */
    private function alreadyGenerated($template, Carbon $date, $period)
    {
        $query = null;
        
        if ($template instanceof Invoice) {
            $query = Invoice::where('company_id', $template->company_id)
                ->where('is_recurring', false); 
        } elseif ($template instanceof Expense) {
            $query = Expense::where('company_id', $template->company_id)
                ->where('expense_name', $template->expense_name)
                ->where('is_recurring', false);
        } elseif ($template instanceof Income) {
            $query = Income::where('company_id', $template->company_id)
                ->where('party_name', $template->party_name)
                ->where('source', '!=', 'standard'); // Standard is for templates
        }

        if (!$query) return false;

        switch ($period) {
            case 'month':
                $query->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month);
                break;
            case 'year':
                $query->whereYear('created_at', $date->year);
                break;
            case 'week':
                $query->whereBetween('created_at', [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()]);
                break;
            case 'quarter':
                $query->whereYear('created_at', $date->year)
                      ->whereRaw('QUARTER(created_at) = ?', [ceil($date->month / 3)]);
                break;
        }

        return $query->exists();
    }

    /**
     * Generate a new Invoice from template.
     */
    private function generateInvoice(Invoice $template, Carbon $date)
    {
        DB::transaction(function () use ($template, $date) {
            $newInvoice = $template->replicate();
            $newInvoice->is_recurring = false; // The copy is a real instance
            $newInvoice->issue_date = $date;
            $newInvoice->due_date = $date->copy()->addDays(7); // Default 7 days
            $newInvoice->status = 'sent';
            
            // Generate new number
            $newInvoice->invoice_number = $newInvoice->generateInvoiceNumber();
            $newInvoice->save();

            // Replicate taxes
            foreach ($template->taxes as $tax) {
                $newTax = $tax->replicate();
                $newTax->taxable_id = $newInvoice->id;
                $newTax->save();
            }

            $this->info("Generated Invoice #{$newInvoice->invoice_number}");
        });
    }

    /**
     * Generate a new Expense from template.
     */
    private function generateExpense(Expense $template, Carbon $date)
    {
        DB::transaction(function () use ($template, $date) {
            $newExpense = $template->replicate();
            $newExpense->source = 'manual'; 
            $newExpense->is_recurring = false;
            $newExpense->due_date = $date;
            $newExpense->status = 'pending';
            $newExpense->parent_id = $template->id;
            $newExpense->save();

            // Replicate taxes
            foreach ($template->taxes as $tax) {
                $newTax = $tax->replicate();
                $newTax->taxable_id = $newExpense->id;
                $newTax->save();
            }

            $this->info("Generated Expense: {$newExpense->expense_name} for {$date->format('M Y')}");
        });
    }

    /**
     * Generate a new Income from template.
     */
    private function generateIncome(Income $template, Carbon $date)
    {
        DB::transaction(function () use ($template, $date) {
            $newIncome = $template->replicate();
            $newIncome->source = 'manual';
            $newIncome->income_date = $date;
            $newIncome->due_date = $date->copy()->addDays(5);
            $newIncome->status = 'pending';
            $newIncome->parent_id = $template->id;
            $newIncome->save();

            // Replicate taxes
            foreach ($template->taxes as $tax) {
                $newTax = $tax->replicate();
                $newTax->taxable_id = $newIncome->id;
                $newTax->save();
            }

            $this->info("Generated Income: {$newIncome->party_name} for {$date->format('M Y')}");
        });
    }
}
