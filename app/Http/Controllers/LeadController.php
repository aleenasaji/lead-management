<?php

namespace App\Http\Controllers;

use App\Enums\LeadStatus;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Http\Requests\UpdateLeadStatusRequest;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function index(Request $request, ?string $filterKey = null): Response|RedirectResponse
    {
        if ($filterKey === null) {
            $filterKey = $this->storeFilters($request, [
                'search' => '',
                'status' => null,
                'sort' => 'desc',
            ]);

            return to_route('leads.index.filtered', $filterKey);
        }

        $filterStates = $request->session()->get('leads.filters', []);
        abort_unless(isset($filterStates[$filterKey]), 404);

        $filters = $filterStates[$filterKey];
        $request->session()->put('leads.active_filter_key', $filterKey);

        $leads = $request->user()
            ->leads()
            ->select(['id', 'name', 'email', 'phone', 'status', 'created_at'])
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = '%'.$filters['search'].'%';

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->when($filters['status'] !== null, fn ($query) => $query->where('status', $filters['status']))
            ->orderBy('created_at', $filters['sort'])
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withPath(route('leads.index.filtered', $filterKey));

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'filters' => $filters,
            'statuses' => LeadStatus::options(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Leads/Form', [
            'lead' => null,
            'statuses' => LeadStatus::options(),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $request->user()->leads()->create($request->validated());

        return $this->redirectToLeadList($request)
            ->with('success', 'Lead created successfully.');
    }

    public function edit(Lead $lead): Response
    {
        $this->authorize('update', $lead);

        return Inertia::render('Leads/Form', [
            'lead' => $lead->only(['id', 'name', 'email', 'phone', 'status']),
            'statuses' => LeadStatus::options(),
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);
        $lead->update($request->validated());

        return $this->redirectToLeadList($request)
            ->with('success', 'Lead updated successfully.');
    }

    public function updateStatus(UpdateLeadStatusRequest $request, Lead $lead): RedirectResponse
    {
        $lead->update($request->validated());

        return $this->redirectToLeadList($request)
            ->with('success', 'Lead status updated successfully.');
    }

    public function destroy(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);
        $lead->delete();

        return $this->redirectToLeadList($request)
            ->with('success', 'Lead deleted successfully.');
    }

    private function storeFilters(Request $request, array $filters): string
    {
        $filterKey = (string) Str::uuid();
        $filterStates = $request->session()->get('leads.filters', []);
        $filterStates[$filterKey] = $filters;

        $request->session()->put('leads.filters', $filterStates);
        $request->session()->put('leads.active_filter_key', $filterKey);

        return $filterKey;
    }

    private function redirectToLeadList(Request $request): RedirectResponse
    {
        $filterKey = $request->session()->get('leads.active_filter_key');

        if (is_string($filterKey) && $request->session()->has("leads.filters.$filterKey")) {
            return to_route('leads.index.filtered', $filterKey);
        }

        return to_route('leads.index');
    }
}
