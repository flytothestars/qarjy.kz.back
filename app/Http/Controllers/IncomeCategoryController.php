<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeCategory\IncomeRequest;
use App\Models\IncomeCategory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncomeCategoryController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'incomeCategories' => IncomeCategory::all()
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
     * @param IncomeRequest $request
     * @return JsonResponse
     */
    public function store(IncomeRequest $request): JsonResponse
    {
        $category = new IncomeCategory($request->all());
        $category->save();
        return response()->json([
            'status' => 'success',
            'incomeCategory' => $category,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\IncomeCategory $incomeCategory
     * @return \Illuminate\Http\Response
     */
    public function show(IncomeCategory $incomeCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\IncomeCategory $incomeCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(IncomeCategory $incomeCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\IncomeCategory $incomeCategory
     * @return \Illuminate\Http\Response
     */
    public function update(IncomeRequest $request, IncomeCategory $incomeCategory): JsonResponse
    {
        $incomeCategory->fill($request->all());
        $incomeCategory->save();
        return response()->json([
            'status' => 'success',
            'incomeCategory' => $incomeCategory,
        ]);
    }

    /**
     * @param IncomeCategory $incomeCategory
     * @return JsonResponse
     */
    public function destroy(IncomeCategory $incomeCategory): JsonResponse
    {
        if (!User::manager()->where("id", auth()->id())->exists()) {
            abort(403);
        }
        $incomeCategory->delete();
        return response()->json([
            'status' => 'success',
        ]);
    }
}
