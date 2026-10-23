<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLeadFiltersRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class LeadFilterController extends Controller
{
    public function store(UpdateLeadFiltersRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $filterKey = (string) Str::uuid();
        $filterStates = $request->session()->get('leads.filters', []);
        $filterStates[$filterKey] = [
            'search' => trim($data['search'] ?? ''),
            'status' => $data['status'] ?? null,
            'sort' => $data['sort'],
        ];

        $request->session()->put('leads.filters', $filterStates);
        $request->session()->put('leads.active_filter_key', $filterKey);

        return to_route('leads.index.filtered', $filterKey);
    }
}
