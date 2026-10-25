<?php

namespace App\Http\Controllers;

use App\Enums\BalanceGroupCode;
use App\Models\BalanceGroup;
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
    public function show(Request $request, Substation $substation): Response
    {
        $year = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);

        $stats = $substation->meterStats()
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->keyBy('balance_group_id');

        $groupIds = BalanceGroup::whereIn('code', array_column(BalanceGroupCode::cases(), 'value'))
            ->pluck('id', 'code');

        $missingGroupIds = $groupIds
            ->reject(fn ($id) => $stats->has($id))
            ->values();

        $latest = $missingGroupIds->isEmpty()
            ? collect()
            : $substation->meterStats()
                ->whereIn('balance_group_id', $missingGroupIds)
                ->where(function ($q) use ($year, $month) {
                    $q->where('year', '<', $year)
                        ->orWhere(fn ($q) => $q->where('year', $year)->where('month', '<', $month));
                })
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->get()
                ->unique('balance_group_id')
                ->keyBy('balance_group_id');

        $rows = collect(BalanceGroupCode::cases())->map(function (BalanceGroupCode $code) use ($stats, $latest, $groupIds) {
            $groupId = $groupIds->get($code->value);
            $current = $stats->get($groupId);
            $last = $latest->get($groupId);

            return [
                'balance_group' => $code->label(),
                'meters_total' => $current->meters_total ?? $last->meters_total ?? 0,
                'meters_installed' => $current->meters_installed ?? 0,
            ];
        });

        $totals = [
            'meters_total' => $rows->sum('meters_total'),
            'meters_installed' => $rows->sum('meters_installed'),
        ];

        return inertia('substations/show', [
            'substation' => $substation,
            'meterStats' => $rows,
            'totals' => $totals,
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
