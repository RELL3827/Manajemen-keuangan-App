<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetRequest;
use App\Models\Budget;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $budgets = $user->budgets()->with('category')->get();
        $categories = $user->categories()->where('type', 'expense')->orderBy('name')->get();

        $enriched = $budgets->map(function (Budget $budget) {
            $spent = $budget->spentAmount();
            $total = (float) $budget->amount;

            return [
                'id' => $budget->id,
                'category' => $budget->category,
                'amount' => $budget->amount,
                'amount_format' => Money::format($budget->amount),
                'spent' => $spent,
                'spent_format' => Money::format($spent),
                'remaining' => max(0, $total - $spent),
                'remaining_format' => Money::format(max(0, $total - $spent)),
                'progress' => $budget->progress,
                'status' => $total > 0 ? ($spent / $total) : 0,
            ];
        });

        return view('budgets.index', [
            'budgets' => $enriched,
            'categories' => $categories,
            'totalBudget' => $user->budgets()->sum('amount'),
        ]);
    }

    public function editInfo(Budget $budget): JsonResponse
    {
        $this->authorize('view', $budget);

        return response()->json([
            'ok' => true,
            'budget' => $budget->only(['id', 'category_id', 'amount', 'period']),
        ]);
    }

    public function store(BudgetRequest $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $data = $request->validated();
        $data['user_id'] = $user->id;

        $period = $request->input('period', 'monthly');

        $data['start_date'] = $period === 'weekly' ? now()->startOfWeek()->toDateString() : now()->startOfMonth()->toDateString();
        $data['end_date'] = $period === 'weekly' ? now()->endOfWeek()->toDateString() : now()->endOfMonth()->toDateString();

        $exists = $user->budgets()->where('category_id', $data['category_id'])->where('period', $period)->exists();

        if ($exists) {
            $message = 'Budget untuk kategori ini sudah ada.';

            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => $message], 422)
                : redirect()->back()->with('error', $message);
        }

        Budget::create($data);

        $message = 'Budget berhasil dibuat.';

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function update(BudgetRequest $request, Budget $budget): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $budget);

        $budget->update($request->validated());

        $message = 'Budget berhasil diperbarui.';

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function destroy(Budget $budget): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $budget);

        $budget->delete();

        $message = 'Budget berhasil dihapus.';

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }
}