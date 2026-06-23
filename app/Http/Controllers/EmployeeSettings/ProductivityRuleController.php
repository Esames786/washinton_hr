<?php

namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\ProductivityRule;
use Illuminate\Http\Request;

class ProductivityRuleController extends Controller
{
    public function index()
    {
        return view('admin.employee_settings.productivity_rules');
    }

    public function list()
    {
        $rules = ProductivityRule::orderByDesc('min_percent')->get();
        return response()->json(['data' => $rules]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $rule = ProductivityRule::create($data);
        return response()->json(['success' => true, 'rule' => $rule]);
    }

    public function update(Request $request, ProductivityRule $productivity_rule)
    {
        $data = $this->validateData($request);
        $productivity_rule->update($data);
        return response()->json(['success' => true, 'rule' => $productivity_rule]);
    }

    public function destroy(ProductivityRule $productivity_rule)
    {
        $productivity_rule->delete();
        return response()->json(['success' => true]);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'label'                => 'required|string|max:100',
            'min_percent'          => 'required|numeric|min:0|max:100',
            'max_percent'          => 'required|numeric|min:0|max:100',
            'attendance_status_id' => 'required|integer|in:2,3,9,5',
            'deduction_percent'    => 'required|numeric|min:0|max:100',
            'status'               => 'nullable|boolean',
        ]);
    }
}
