<?php

namespace App\Http\Controllers;

use App\Http\Requests\Bill\BillStore;
use App\Models\Bill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillController extends Controller
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
     * @param BillStore $request
     * @return JsonResponse
     */
    public function store(BillStore $request): JsonResponse
    {
        try {
            $bill = new Bill($request->all());
            $bill->save();
        } catch (\Error|\Exception $exception) {
            return $this->clear();
        }
        return response()->json([
            'status' => 'success',
            'bill' => $bill,
        ]);
    }

    protected function clear(): JsonResponse
    {
        Bill::where("user_id", auth()->id())
            ->where("url", request()->get('url'))
            ->delete();
        return response()->json([
            'errors' => [
                'url' => "Не удалось обработать чек. Пришлите этот чек нам на e-mail, мы обязательно научимся его распознавать"
            ]
        ], 422);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Bill $bill
     * @return \Illuminate\Http\Response
     */
    public function show(Bill $bill)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Bill $bill
     * @return \Illuminate\Http\Response
     */
    public function edit(Bill $bill)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Bill $bill
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bill $bill)
    {
        //
    }

    /**
     * @param Bill $bill
     * @return JsonResponse
     */
    public function destroy(Bill $bill): JsonResponse
    {
        if (auth()->id() !== $bill->user_id) {
            abort(403);
        }
        $bill->delete();
        return response()->json([
            'status' => 'success'
        ]);
    }
}
