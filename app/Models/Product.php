<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['root_category_id', 'secondary_category_id', 'final_category_id', 'title'];

    public static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub

        self::created(function (Product $model) {
            $model->autoMoveToCategory();
        });
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    public function expenseRootCategory()
    {
        return $this->belongsTo(ExpenseCategory::class, 'root_category_id');
    }

    public function expenseSecondaryCategory()
    {
        return $this->belongsTo(ExpenseCategory::class, 'secondary_category_id');
    }

    public function expenseFinalCategory()
    {
        return $this->belongsTo(ExpenseCategory::class, 'final_category_id');
    }

    public function findRootCategory(): ExpenseCategory|null
    {
        $tags = Tag::whereHas("category", function ($q) {
            $q->root();
        })->get();
        return ExpenseCategory::findByTags($tags, $this->title);
    }

    public function findSecondaryCategory(): ExpenseCategory|null
    {
        $tags = Tag::whereHas("category", function ($q) {
            $q->secondary();
        })->get();
        return ExpenseCategory::findByTags($tags, $this->title);
    }

    public function findFinalCategory(): ExpenseCategory|null
    {
        $tags = Tag::whereHas("category", function ($q) {
            $q->final();
        })->get();
        return ExpenseCategory::findByTags($tags, $this->title);
    }

    public function autoMoveToCategory()
    {
        $same = Product::query()->where("id", "!=", $this->id)->where("title", $this->title)->first();
        if ($same) {
            $this->final_category_id = $same->final_category_id;
            $this->secondary_category_id = $same->secondary_category_id;
            $this->root_category_id = $same->root_category_id;
            $this->save();
            return;
        }

        $final = $this->findFinalCategory();
        if ($final) {
            $this->final_category_id = $final->id;
            $this->secondary_category_id = $final->parent_id;
            $this->root_category_id = $final->parent?->parent_id;
        } else {
            $secondary = $this->findSecondaryCategory();
            if ($secondary) {
                $serviceCategory = ExpenseCategory::final()->service()->first();
                $this->final_category_id = $serviceCategory->id;
                $this->secondary_category_id = $secondary->id;
                $this->root_category_id = $secondary->parent_id;
            } else {
                $root = $this->findRootCategory();
                if ($root || $this->root_category_id) {
                    $serviceCategory = ExpenseCategory::secondary()->service()->first();
                    $this->secondary_category_id = $serviceCategory->id;
                    $this->root_category_id = $this->root_category_id ?: $root->id;
                } else {
                    $serviceCategory = ExpenseCategory::root()->service()->first();
                    $this->root_category_id = $serviceCategory->id;
                }
            }
        }
        $this->save();
    }

    public function syncAllTransactions()
    {
        $product = $this;
        Transaction::query()
            ->where("product_id", $product->id)
            /* ->where(function ($query) use ($product) {
                 $query->where("expense_root_category_id", "!=", $product->root_category_id)
                     ->orWhere("expense_secondary_category_id", "!=", $product->secondary_category_id)
                     ->orWhere("expense_final_category_id", "!=", $product->final_category_id);
             })*/
            ->update([
                'expense_root_category_id' => $product->root_category_id,
                'expense_secondary_category_id' => $product->secondary_category_id,
                'expense_final_category_id' => $product->final_category_id,
            ]);
    }
}