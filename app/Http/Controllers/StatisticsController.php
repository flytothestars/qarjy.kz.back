<?php

namespace App\Http\Controllers;

use App\Http\Requests\Statistics\InRageRequest;
use App\Http\Requests\Statistics\TotalRequest;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function stats(Request $request)
    {
        $user = auth()->user();
        // income
        $incomes = IncomeCategory::query()->withSum(['transactions' => function ($q) use ($request, $user) {
            $q->where('user_id', $user->id)->fromDate($request->from)->toDate($request->to);
        }], 'amount')->with(['transactions' => function ($q) use ($request, $user) {
            $q->where('user_id', $user->id)->fromDate($request->from)->toDate($request->to);
        }])->get();
        $totalIncome = $incomes->sum("transactions_sum_amount");
        $incomes = $incomes->map(function ($income) {
            $byCompany = $income->transactions->groupBy(function ($item) {
                return mb_strtolower($item['company']);
            })->map(function ($companyTransactions) {
                return [
                    'company' => $companyTransactions->first()?->company,
                    'company_amount' => $companyTransactions->sum("amount")
                ];
            })->values();
            $income->company_incomes = $byCompany;
            return $income->only(['id', 'title', 'created_at', 'transactions_sum_amount', 'company_incomes']);
        });

        // expense
        $expenses = ExpenseCategory::query()->root()->orderBy('isService')->orderBy('title')->withSum(['rootTransactions' => function ($q) use ($request, $user) {
            $q->where('user_id', $user->id)->fromDate($request->from)->toDate($request->to);
        }], 'amount')->get();
        $totalExpense = $expenses->sum("root_transactions_sum_amount");
        return response()->json([
            'status' => 'success',
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'incomeCategories' => $incomes,
            'expenseCategories' => $expenses,
        ]);
    }

    public function total(TotalRequest $request)
    {
        $user = auth()->user();
        $transactions = $user->transactions()->fromDate($request->from)->toDate($request->to);
        $expenseBuilder = clone $transactions;
        $incomeBuilder = clone $transactions;
        $expense = $expenseBuilder->expense()->sum("amount");
        $income = $incomeBuilder->income()->sum("amount");

        return response()->json([
            'status' => 'success',
            'expense' => $expense,
            'income' => $income,
        ]);
    }

    public function inRange(InRageRequest $request)
    {
        switch ($request->step) {
            case "day":
                $data = $this->getDailyData($request->from, $request->to);
                break;
            /*case "week":
                $data = $this->getWeeklyData($request->from, $request->to);
                break;*/
            case "month":
                $data = $this->getMonthlyData($request->from, $request->to);
                break;

            default:
                abort(400);

        }
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    protected function getDailyData($from, $to)
    {
        $user = auth()->user();
        $from = Carbon::parse($from)->startOfDay();
        $initDate = clone $from;
        $to = Carbon::parse($to)->endOfDay();
        $data = [];
        while ($from <= $to) {
            $cellFrom = Carbon::parse($from)->startOfDay(); # 14, 0:0
            $cellTo = Carbon::parse($cellFrom)->addDay();
            $income = $user->transactions()->income()->fromDate($initDate)->toDate($cellTo)->sum("amount");
            $expense = $user->transactions()->expense()->fromDate($initDate)->toDate($cellTo)->sum("amount");
            $data[] = [
                'from' => $cellFrom,
                'to' => $cellTo,
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense
            ];
            $from->addDay();
        }
        return $data;
    }

    /*protected function getWeeklyData($from, $to)
    {
        $user = auth()->user();
        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();
        $data = [];
        while ($from <= $to) {
            $cellFrom = Carbon::parse($from)->startOfDay(); # 14, 0:0
            $cellTo = Carbon::parse($cellFrom)->addWeek();
            $income = $user->transactions()->income()->fromDate($cellFrom)->toDate($cellTo)->sum("amount");
            $expense = $user->transactions()->expense()->fromDate($cellFrom)->toDate($cellTo)->sum("amount");
            $data[] = [
                'from' => $cellFrom,
                'to' => $cellTo,
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense
            ];
            $from->addWeek();
        }
        return $data;
    }*/

    protected function getMonthlyData($from, $to)
    {
        $user = auth()->user();
        $from = Carbon::parse($from)->startOfMonth();
        $initDate = clone $from;
        $to = Carbon::parse($to)->endOfMonth();
        $data = [];
        while ($from <= $to) {
            $cellFrom = Carbon::parse($from)->startOfMonth(); # 14, 0:0
            $cellTo = Carbon::parse($cellFrom)->addMonth();
            $income = $user->transactions()->income()->fromDate($initDate)->toDate($cellTo)->sum("amount");
            $expense = $user->transactions()->expense()->fromDate($initDate)->toDate($cellTo)->sum("amount");
            $data[] = [
                'from' => $cellFrom,
                'to' => $cellTo,
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense
            ];
            $from->addMonth();
        }
        return $data;
    }

}
