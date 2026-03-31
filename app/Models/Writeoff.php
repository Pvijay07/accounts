<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Writeoff extends Model
{
    protected $fillable = [
        'invoice_id',
        'company_id',
        'amount',
        'reason',
        'writeoff_date',
        'description',
        'created_by'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}