<?php

namespace App\Http\Controllers;

use App\Actions\RecordMeterInstallations;
use App\Enums\BalanceGroupCode;
use App\Http\Requests\StoreSubstationMeterStatRequest;
use App\Models\Substation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubstationMeterStatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(
        StoreSubstationMeterStatRequest $request,
        Substation $substation,
        RecordMeterInstallations $record,
    ): RedirectResponse {
        $validated = $request->validated();

        $record(
            $substation,
            BalanceGroupCode::from($validated['balance_group']),
            $validated['year'],
            $validated['month'],
            $validated['amount'],
        );

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
