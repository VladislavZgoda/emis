<?php

namespace App\Http\Controllers;

use App\Models\Substation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SubstationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:15', 'unique:substations,name'],
        ]);

        Substation::create($validated);

        return Inertia::flash('message', 'Подстанция успешно создана!')->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Substation $substation): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Substation $substation): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:15', Rule::unique('substations', 'name')->ignore($substation)],
        ]);

        $substation->update($validated);

        return Inertia::flash('message', 'Подстанция переименована.')->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Substation $substation): RedirectResponse
    {
        $substation->delete();

        return Inertia::flash('message', 'Подстанция успешно удалена!')->back();
    }
}
