<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseCategory\ExprenseCategoryStore;
use App\Models\ExpenseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $parentId = $request->parent_id;
        $user = auth()->user();
        $categories = ExpenseCategory::query()->withCount(['children', 'rootTransactions' => function ($q) use ($user) {
            if ($user && $user->isManager()) {
                $q->withTrashed();
            }
        }, 'secondaryTransactions' => function ($q) use ($parentId, $user) {
            if ($user && $user->isManager()) {
                $q->withTrashed();
            }
            return $q->where("expense_root_category_id", $parentId);
        }, 'finalTransactions' => function ($q) use ($parentId, $user) {
            if ($user && $user->isManager()) {
                $q->withTrashed();
            }
            return $q->where('expense_secondary_category_id', $parentId);
        }]);

        if ($request->has("level")) {
            $categories = $categories->where("level", $request->level);
            switch ($request->level) {
                case 0:
                    $categories = $categories->withCount(['secondaryServiceTransactions']);
                    break;
                case 1:
                    $categories = $categories->withCount(['finalServiceTransactions']);
                    break;
            }
        }

        if ($parentId) {
            $categories = $categories->where(function ($q) use ($parentId) {
                $q->where("parent_id", $parentId)->orWhereNull("parent_id");
            });
        }

        $categories = $categories->orderBy("isService")->get();

        if ($user) {
            $transactions = $user->transactions();

            $transactions = $transactions
                ->fromDate($request->from)
                ->toDate($request->to)
                ->forCategories($categories->pluck("id"))
                ->get();
            $categories = $categories->map(function ($category) use ($transactions, $parentId) {
                switch ($category->level) {
                    case 0:
                        $category->expense = round($transactions->where("expense_root_category_id", $category->id)->sum("amount"), 2);
                        break;
                    case 1:
                        $category->expense = round($transactions->where('expense_root_category_id', $parentId)->where("expense_secondary_category_id", $category->id)->sum("amount"), 2);
                        break;
                    case 2:
                        $category->expense = round($transactions->where('expense_secondary_category_id', $parentId)->where("expense_final_category_id", $category->id)->sum("amount"), 2);
                        break;
                    default:
                        $category->expense = 0;
                }
                return $category;
            });
        }

        return response()->json([
            'status' => 'success',
            'expenseCategories' => $categories,
            'totalExpense' => $categories->sum('expense'),
            'balance' => $user->balance,
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
     * @param ExprenseCategoryStore $request
     * @return JsonResponse
     */
    public function store(ExprenseCategoryStore $request): JsonResponse
    {
        $category = new ExpenseCategory($request->all());
        $category->save();
        return response()->json([
            'status' => 'success',
            'expenseCategory' => $category,
        ]);
    }

    /**
     * @param ExpenseCategory $expenseCategory
     * @return JsonResponse
     */
    public function show(ExpenseCategory $expenseCategory, Request $request): JsonResponse
    {
        $parentId = $request->parent_id ?: $expenseCategory->parent_id;

        $page = (int)$request->page ?: 1;
        $take = (int)$request->take ?: 30;
        $skip = ($page - 1) * $take;

        $user = auth()->user();
        if ($user->isManager()) {
            $expenseCategory->load(["rootProducts" => function ($q) use ($parentId, $user, $skip, $take) {
                $q->orderBy("id", "asc")->skip($skip)->take($take);
            }, 'secondaryProducts' => function ($q) use ($parentId, $user, $skip, $take) {
                $q->where('root_category_id', $parentId)->skip($skip)->take($take);
            }, 'finalProducts' => function ($q) use ($parentId, $user, $skip, $take) {
                $q->where('secondary_category_id', $parentId)->skip($skip)->take($take);
            }]);
        } else {
            $expenseCategory->load(["rootTransactions" => function ($q) use ($parentId, $user, $skip, $take) {
                $q->orderBy("id", "asc")->skip($skip)->take($take);
            }, 'secondaryTransactions' => function ($q) use ($parentId, $user, $skip, $take) {
                $q->where('expense_root_category_id', $parentId)->skip($skip)->take($take);
            }, 'finalTransactions' => function ($q) use ($parentId, $user, $skip, $take) {
                $q->where('expense_secondary_category_id', $parentId)->skip($skip)->take($take);
            }]);
        }

        if (!$expenseCategory->isService) {
            $expenseCategory->load("tags");
        }

        return response()->json([
            'status' => 'success',
            'expenseCategory' => $expenseCategory,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ExpenseCategory $expenseCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(ExpenseCategory $expenseCategory)
    {
        //
    }

    /**
     * @param Request $request
     * @param ExpenseCategory $expenseCategory
     * @return JsonResponse
     */
    public function update(Request $request, ExpenseCategory $expenseCategory): JsonResponse
    {
        $expenseCategory->fill($request->all());
        $expenseCategory->save();
        return response()->json([
            'status' => 'success',
            'expenseCategory' => $expenseCategory,
        ]);
    }

    /**
     * @param ExpenseCategory $expenseCategory
     * @return JsonResponse
     */
    public function destroy(ExpenseCategory $expenseCategory): JsonResponse
    {
        $expenseCategory->delete();
        return response()->json([
            'status' => 'success'
        ]);
    }
}
