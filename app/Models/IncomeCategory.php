<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeCategory extends Model
{
    use HasFactory;

    protected $fillable = ['title'];

    /*protected $casts=[
        'company'=>'string'
    ];*/

    public function transactions(){
        return $this->hasMany(Transaction::class,'income_category_id');
    }

    public function getTransactionsSumAmountAttribute($val)
    {
        return $val ?? 0;
    }

}
