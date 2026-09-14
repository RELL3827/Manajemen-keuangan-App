<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $categories = $user->categories()->withCount('transactions')->orderBy('type')->orderBy('name')->get();
        $income = $categories->where('type', 'income')->values();
        $expense = $categories->where('type', 'expense')->values();

        return view('categories.index', compact('income', 'expense'));
    }

    public function editInfo(Category $category): JsonResponse
    {
        $this->authorize('view', $category);

        return response()->json([
            'ok' => true,
            'category' => $category->only(['id', 'name', 'type', 'icon', 'color', 'is_default']),
        ]);
    }

    public function store(CategoryRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        $exists = Auth::user()->categories()
            ->where('name', $data['name'])
            ->where('type', $data['type'])
            ->exists();

        if ($exists) {
            $message = 'Kategori dengan nama tersebut sudah ada.';

            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => $message], 422)
                : redirect()->back()->with('error', $message);
        }

        $category = Category::create($data);

        $message = 'Kategori berhasil dibuat.';

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message, 'category' => $category]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function update(CategoryRequest $request, Category $category): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update($request->validated());

        $message = 'Kategori berhasil diperbarui.';

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function destroy(Category $category): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $category);

        if (Transaction::where('user_id', Auth::id())->where('category_id', $category->id)->exists()) {
            $message = 'Kategori tidak dapat dihapus karena sudah dipakai transaksi.';
            if (request()->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $message], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        $category->delete();

        $message = 'Kategori berhasil dihapus.';

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }
}