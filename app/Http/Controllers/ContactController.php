<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\ContactRequest;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin', ['except' => ['index']]);
    }

    /**
     * @return JsonResponse
     */
    public function index():JsonResponse
    {
        return response()->json([
            'status'=>'success',
            'contacts'=>Contact::all()
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
     * @param ContactRequest $request
     * @return JsonResponse
     */
    public function store(ContactRequest $request):JsonResponse
    {
        $contact = new Contact($request->all());
        $contact->save();
        return response()->json([
            'status'=>'success',
            'contact'=>$contact,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Contact $contact
     * @return \Illuminate\Http\Response
     */
    public function show(Contact $contact)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Contact $contact
     * @return \Illuminate\Http\Response
     */
    public function edit(Contact $contact)
    {
        //
    }

    /**
     *
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Contact $contact
     * @return \Illuminate\Http\Response
     */
    public function update(ContactRequest $request, Contact $contact):JsonResponse
    {
        $contact->fill($request->all());
        $contact->save();
        return response()->json([
            'status'=>'success',
            'contact'=>$contact,
        ]);
    }

    /**
     * @param Contact $contact
     * @return JsonResponse
     */
    public function destroy(Contact $contact):JsonResponse
    {
        $contact->delete();
        return response()->json([
            'status'=>'success'
        ]);
    }
}
