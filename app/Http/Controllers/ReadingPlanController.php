<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\Request;

class ReadingPlanController extends Controller
{
    public function index(Request $request)
    {
        $currentStatus = $request->status;

        $readingPlans = auth()->user()
            ->readingPlans()
            ->with('book');

        if ($currentStatus) {
            $readingPlans->where('status', $currentStatus);
        }

        $readingPlans = $readingPlans
            ->latest()
            ->get();

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    public function create()
    {
        $books = Book::all();

        return view('reading-plans.create', compact('books'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'target_date' => ['required', 'date'],
        ]);

        auth()->user()->readingPlans()->create([
            'book_id' => $validated['book_id'],
            'target_date' => $validated['target_date'],
            'status' => 'planned',
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を登録しました。');
    }

    public function complete(ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読了として登録しました。');
    }

    public function edit(ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    public function update(Request $request, ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        $validated = $request->validate([
            'target_date' => ['required', 'date'],
        ]);

        $readingPlan->update($validated);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を更新しました。');
    }

    public function destroy(ReadingPlan $readingPlan)
    {
        $this->authorize('delete', $readingPlan);
        $readingPlan->delete();

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }
}
