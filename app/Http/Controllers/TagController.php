<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tag\StoreTag;
use App\Models\ExpenseCategory;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param ExpenseCategory $expenseCategory
     * @param StoreTag $request
     * @return JsonResponse
     */
    public function store(ExpenseCategory $expenseCategory, StoreTag $request): JsonResponse
    {
        $tag = new Tag();
        $tag->text = $request->text;
        $tag->expense_category_id = $expenseCategory->id;
        $tag->save();
        return response()->json([
            'status' => 'success',
            'tag' => $tag,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Tag $tag
     * @return \Illuminate\Http\Response
     */
    public function show(Tag $tag)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Tag $tag
     * @return \Illuminate\Http\Response
     */
    public function edit(Tag $tag)
    {
        //
    }

    /**
     * @param Request $request
     * @param ExpenseCategory $category
     * @param Tag $tag
     * @return JsonResponse
     */
    public function update(Request $request, ExpenseCategory $expenseCategory, Tag $tag):JsonResponse
    {
        $tag->fill($request->all());
        $tag->save();
        return response()->json([
            'status'=>'success'
        ]);
    }

    /**
     * @param ExpenseCategory $category
     * @param Tag $tag
     * @return JsonResponse
     */
    public function destroy(ExpenseCategory $expenseCategory,Tag $tag):JsonResponse
    {
        $tag->delete();
        return response()->json([
            'status'=>'success'
        ]);
    }
}
