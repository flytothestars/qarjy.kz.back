<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $users = User::customer()->withCount('expenseTransactions', 'incomeTransactions');

        $page = $request->page ?: 1;
        $take = $request->take ?: 30;
        $skip = ($page - 1) * $take;
        $pagesCount = ceil($users->count() / $take);

        $users = $users->orderBy('created_at')->skip($skip)->take($take)->get();
        return response()->json([
            'status' => 'success',
            'users' => $users,
            'pagesCount' => $pagesCount,
        ]);
    }
}
