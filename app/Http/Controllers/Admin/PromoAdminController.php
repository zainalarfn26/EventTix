<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromoAdminController extends Controller
{
    public function index(Request $request)
    {
        $promos = Promo::query()
            ->when($request->filled('q'), fn ($q) => $q->where('code', 'like', '%' . $request->q . '%'))
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total' => Promo::count(),
            'active' => Promo::get()->filter->isValid()->count(),
            'usages' => Promo::sum('current_usages'),
        ];

        return view('admin.promos.index', compact('promos', 'stats'));
    }

    public function store(Request $request)
    {
        $request->merge(['code' => strtoupper(trim((string) $request->code))]);
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');

        Promo::create($data);

        return redirect()->route('admin.promos.index')->with('success', 'Kode promo ' . $data['code'] . ' berhasil dibuat.');
    }

    public function update(Request $request, Promo $promo)
    {
        $request->merge(['code' => strtoupper(trim((string) $request->code))]);
        $data = $this->validated($request, $promo);
        $data['is_active'] = $request->boolean('is_active');

        $promo->update($data);

        return redirect()->route('admin.promos.index')->with('success', 'Kode promo ' . $promo->code . ' berhasil diperbarui.');
    }

    public function toggle(Promo $promo)
    {
        $promo->update(['is_active' => !$promo->is_active]);

        return back()->with('success', 'Promo ' . $promo->code . ($promo->is_active ? ' diaktifkan.' : ' dinonaktifkan.'));
    }

    public function destroy(Promo $promo)
    {
        $code = $promo->code;
        $promo->delete();

        return redirect()->route('admin.promos.index')->with('success', 'Kode promo ' . $code . ' berhasil dihapus.');
    }

    protected function validated(Request $request, ?Promo $promo = null): array
    {
        $rules = [
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('promos', 'code')->ignore($promo?->id)],
            'discount_type' => 'required|in:percentage,fixed',
            'amount' => 'required|integer|min:1',
            'max_usages' => 'nullable|integer|min:1',
            'valid_until' => 'nullable|date',
        ];

        if ($request->discount_type === 'percentage') {
            $rules['amount'] = 'required|integer|min:1|max:100';
        }

        $data = $request->validate($rules);

        if ($promo && isset($data['max_usages']) && $data['max_usages'] < $promo->current_usages) {
            back()->withInput()->withErrors(['max_usages' => 'Batas pemakaian tidak boleh kurang dari pemakaian saat ini (' . $promo->current_usages . ').'])->throwResponse();
        }

        return $data;
    }
}
