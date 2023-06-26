<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\SearchRequest;
use App\Http\Requests\Transaction\StoreTransaction;
use App\Http\Requests\Transaction\UpdateTransaction;
use App\Models\Bill;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $user = auth()->user();
        $transactions = $user->transactions()->orderBy('transaction_date', 'desc')->with(['expenseRootCategory' => function ($q) {
            $q->select('expense_categories.id', 'expense_categories.title', 'expense_categories.icon');
        }, 'expenseSecondaryCategory' => function ($q) {
            $q->select('expense_categories.id', 'expense_categories.title', 'expense_categories.icon');
        }, 'expenseFinalCategory' => function ($q) {
            $q->select('expense_categories.id', 'expense_categories.title', 'expense_categories.icon');
        }, 'incomeCategory' => function ($q) {
            $q->select('income_categories.id', 'income_categories.title');
        }, 'bill' => function ($q) {
            $q->select("bills.id", "bills.fiscal_id", "bills.bill_date");
        }]);

        $transactions = $transactions->fromDate($request->from)->toDate($request->to);

        if ($request->expense_root_category_id) {
            $transactions = $transactions->where("expense_root_category_id", $request->expense_root_category_id);
        }

        if ($request->expense_secondary_category_id) {
            $transactions = $transactions->where("expense_secondary_category_id", $request->expense_secondary_category_id);
        }

        if ($finalIds = array_filter(explode(',', $request->expense_final_category_id))) {
            $transactions = $transactions->whereIn("expense_final_category_id", $finalIds);
        }

        if ($type = $request->type) {
            $transactions = $transactions->where("type", $type);
        }

        $totalCount = $transactions->count();

        if ($request->paginate) {
            $page = (int)$request->page ?: 1;
            $take = (int)$request->take ?: 30;
            $skip = ($page - 1) * $take;
            $pagesCount = ceil($totalCount / $take);
            $transactions = $transactions->skip($skip)->take($take);
        }

        $transactions = $transactions->get();

        return response()->json([
            'status' => 'success',
            'transactions' => $transactions,
            'totalAmount' => $transactions->sum("amount"),
            'balance' => $user->balance,
            'totalCount' => $totalCount,
            'pagesCount' => $pagesCount ?? 0,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * @param StoreTransaction $request
     * @return JsonResponse
     */
    public function store(StoreTransaction $request): JsonResponse
    {
        $transaction = new Transaction($request->all());
        $transaction->save();
        return response()->json([
            'status' => 'success',
            'transaction' => $transaction,
        ]);
    }

    /**
     * @param Transaction $transaction
     * @return JsonResponse
     */
    public function show(Transaction $transaction): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'transaction' => $transaction
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Transaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * @param UpdateTransaction $request
     * @param Transaction $transaction
     * @return JsonResponse
     */
    public function update(UpdateTransaction $request, $transactionId): JsonResponse
    {
        $transaction = Transaction::withTrashed()->findOrFail($transactionId);
        $transaction->fill($request->all());
        $transaction->save();

        $transaction->load(['expenseRootCategory' => function ($q) {
            $q->select('expense_categories.id', 'expense_categories.title', 'expense_categories.icon');
        }, 'expenseSecondaryCategory' => function ($q) {
            $q->select('expense_categories.id', 'expense_categories.title', 'expense_categories.icon');
        }, 'expenseFinalCategory' => function ($q) {
            $q->select('expense_categories.id', 'expense_categories.title', 'expense_categories.icon');
        }, 'incomeCategory' => function ($q) {
            $q->select('income_categories.id', 'income_categories.title');
        }, 'bill' => function ($q) {
            $q->select("bills.id", "bills.fiscal_id", "bills.bill_date");
        }]);
        return response()->json([
            'status' => 'success',
            'transaction' => $transaction
        ]);
    }

    /**
     * @param Transaction $transaction
     * @return JsonResponse
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        if (auth()->id() !== $transaction->user_id) {
            abort(403);
        }
        $transaction->delete();
        return response()->json([
            'status' => 'success'
        ]);
    }
}
