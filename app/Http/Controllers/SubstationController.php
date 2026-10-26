<?php

namespace App\Http\Controllers;

use App\Actions\BuildSubstationMeterReport;
use App\Http\Requests\ShowSubstationRequest;
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
    public function show(
        ShowSubstationRequest $request,
        Substation $substation,
        BuildSubstationMeterReport $buildReport
    ): Response {
        $year = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);

        $rows = $buildReport($substation, $year, $month);

        return inertia('substations/show', [
            'substation' => $substation->only(['id', 'name']),
            'meterStats' => $rows,
            'totals' => [
                'meters_total' => $rows->sum('meters_total'),
                'meters_installed' => $rows->sum('meters_installed'),
            ],
            'filters' => [
                'year' => $year,
                'month' => $month,
            ],
        ]);
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
