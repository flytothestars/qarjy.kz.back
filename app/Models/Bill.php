<?php

namespace App\Models;

use App\Services\BillParser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Bill extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['url', 'number', 'bill_date'];

    protected $appends = ['type'];

    const DEFAULT_TIME_OFFSET = 6;

    public static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub

        self::creating(function ($model) {
            $model->user_id = auth()->id();
        });

        self::created(function (Bill $model) {
            $model->parse();
        });

        self::deleting(function ($model) {
            $model->transactions()->get()->each(function ($transaction) {
                $transaction->delete();
            });
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeFromDate($query, string|null $date)
    {
        if (!$date) {
            return $query;
        }
        return $query->where("created_at", ">=", Carbon::parse($date)->startOfDay());
    }

    public function scopeToDate($query, $date)
    {
        if (!$date) {
            return $query;
        }
        return $query->where("created_at", "<=", Carbon::parse($date)->endOfDay());
    }

    public function getTypeAttribute(): string
    {
        return 'bill';
    }

    public function parse()
    {
        $parser = new BillParser();
        $parser->loadBill($this->url);
        $data = $parser->getData();

        $this->bill_date = Carbon::parse($data['date'])->format("d.m.Y H:i");
        $this->fiscal_id = $data['billNumber'];
        $this->save();

        #$category = ExpenseCategory::root()->service()->first();
        $insrt = [];

        $UTC_OFFSET = request()->get("utc_offset") ?? Bill::DEFAULT_TIME_OFFSET;
        foreach ($data['items'] ?? [] as $k => $item) {
            $insrt[] = [
                'title' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'amount' => $item['amount'],
                'user_id' => $this->user_id,
                'company' => $data['company'],
                'transaction_date' => Carbon::parse($data['date'])->subHours($UTC_OFFSET),
                'type' => 'expense',
                #'expense_root_category_id' => $category->id ?? null,
                'bill_id' => $this->id,
            ];
        }
        DB::beginTransaction();
        Transaction::insert($insrt);
        $expense = array_sum(array_column($insrt, 'amount'));
        $user = User::find($this->user_id);
        if ($user) {
            $user->updateBalance($expense, 0);
        }
        DB::commit();

        $this->autoMoveTransactions();
    }

    public function autoMoveTransactions()
    {
        $transactions = $this->transactions()->where(function($q){
            $q->whereHas("expenseRootCategory", function ($q) {
                $q->service();
            })->orWhereNull('expense_root_category_id');
        })->get();
        $transactions->each(function (Transaction $transaction) {
            $transaction->distribute();
        });
    }
}
