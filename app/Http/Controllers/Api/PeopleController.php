<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeopleRequest;
use App\Models\People;

class PeopleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $people = People::all();
        return $this->jsonResponse(data: ['people' => $people]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PeopleRequest $peopleRequest)
    {
        $people = new People();
        $people->name = $peopleRequest->name;
        $people->save();
        return $this->jsonResponse(message: 'People created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(People $people)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(People $people)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PeopleRequest $peopleRequest, People $people)
    {
        $people->name = $peopleRequest->name;
        $people->update();
        return $this->jsonResponse(message: 'People updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(People $people)
    {
        //
    }
}
