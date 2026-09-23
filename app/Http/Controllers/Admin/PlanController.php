<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount([
            'markets as active_markets_count' => fn ($q) => $q->whereIn('subscription_status', ['trial', 'active']),
        ])->orderBy('sort_order')->orderBy('monthly_price')->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.form', ['plan' => new Plan(['trial_days' => 14, 'trial_sms_credits' => 20, 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']);

        DB::transaction(function () use ($data) {
            if ($data['is_default']) {
                Plan::query()->update(['is_default' => false]);
            }
            Plan::create($data);
        });

        return redirect()->route('admin.plans.index')->with('success', __('Plan created.'));
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $this->validated($request, $plan);

        DB::transaction(function () use ($data, $plan) {
            if ($data['is_default']) {
                Plan::where('id', '!=', $plan->id)->update(['is_default' => false]);
            }
            $plan->update($data);
        });

        return redirect()->route('admin.plans.index')->with('success', __('Plan updated.'));
    }

    public function destroy(Plan $plan)
    {
        if ($plan->subscriptions()->exists() || $plan->markets()->exists()) {
            return back()->with('error', __('This plan is in use and cannot be deleted. Deactivate it instead.'));
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')->with('success', __('Plan deleted.'));
    }

    protected function validated(Request $request, ?Plan $plan = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('plans', 'name')->ignore($plan?->id)],
            'name_bn' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['nullable', 'numeric', 'min:0'],
            'shop_limit' => ['nullable', 'integer', 'min:1'],
            'sms_credits_per_month' => ['required', 'integer', 'min:0'],
            'trial_days' => ['required', 'integer', 'min:0', 'max:365'],
            'trial_sms_credits' => ['required', 'integer', 'min:0'],
            'features' => ['nullable', 'array'],
            'features.*' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $features = [];
        foreach (array_keys(Plan::FEATURES) as $feature) {
            $features[$feature] = (bool) ($data['features'][$feature] ?? false);
        }

        $data['features'] = $features;
        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['shop_limit'] = $data['shop_limit'] ?? null;
        $data['yearly_price'] = $data['yearly_price'] ?? null;

        return $data;
    }
}
