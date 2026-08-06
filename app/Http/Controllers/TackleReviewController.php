<?php

namespace App\Http\Controllers;

use App\Models\TackleReview;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TackleReviewController extends Controller
{
    public function index(): View
    {
        $reviews = TackleReview::query()
            ->published()
            ->with(['user', 'photos'])
            ->latest()
            ->paginate(12);

        return view('tackle-reviews.index', compact('reviews'));
    }

    public function create(): View
    {
        $this->authorize('create', TackleReview::class);

        return view('tackle-reviews.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TackleReview::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:0', 'max:5'],
            'body' => ['required', 'string', 'max:5000'],
            'purchase_url' => ['nullable', 'url', 'max:2048'],
            'photos' => ['nullable', 'array', 'max:6'],
            'photos.*' => ['image', 'max:5120'],
        ]);

        $review = TackleReview::query()->create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'brand' => $validated['brand'] ?? null,
            'rating' => (int) $validated['rating'],
            'body' => $validated['body'],
            'purchase_url' => $validated['purchase_url'] ?? null,
            'is_published' => true,
            'featured_on_home' => false,
        ]);

        foreach ($request->file('photos', []) as $index => $photo) {
            $path = Uploads::store($photo, 'tackle-review-photos');
            $review->photos()->create([
                'image_path' => $path,
                'sort_order' => $index,
            ]);
        }

        return redirect()
            ->route('tackle-reviews.show', $review)
            ->with('status', 'Tackle review published.');
    }

    public function show(TackleReview $tackleReview): View
    {
        abort_unless(
            $tackleReview->is_published || (auth()->user()?->can('update', $tackleReview) ?? false),
            404
        );

        $tackleReview->load(['user', 'photos']);

        return view('tackle-reviews.show', ['review' => $tackleReview]);
    }

    public function edit(TackleReview $tackleReview): View
    {
        $this->authorize('update', $tackleReview);

        $tackleReview->load('photos');

        return view('tackle-reviews.edit', ['review' => $tackleReview]);
    }

    public function update(Request $request, TackleReview $tackleReview): RedirectResponse
    {
        $this->authorize('update', $tackleReview);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:0', 'max:5'],
            'body' => ['required', 'string', 'max:5000'],
            'purchase_url' => ['nullable', 'url', 'max:2048'],
            'photos' => ['nullable', 'array', 'max:6'],
            'photos.*' => ['image', 'max:5120'],
            'remove_photo_ids' => ['nullable', 'array'],
            'remove_photo_ids.*' => ['integer'],
        ]);

        $tackleReview->update([
            'title' => $validated['title'],
            'brand' => $validated['brand'] ?? null,
            'rating' => (int) $validated['rating'],
            'body' => $validated['body'],
            'purchase_url' => $validated['purchase_url'] ?? null,
        ]);

        $removeIds = collect($request->input('remove_photo_ids', []))->map(fn ($id) => (int) $id)->all();
        if ($removeIds !== []) {
            $tackleReview->photos()->whereIn('id', $removeIds)->get()->each->delete();
        }

        $sort = (int) $tackleReview->photos()->max('sort_order');
        foreach ($request->file('photos', []) as $photo) {
            $path = Uploads::store($photo, 'tackle-review-photos');
            $tackleReview->photos()->create([
                'image_path' => $path,
                'sort_order' => ++$sort,
            ]);
        }

        return redirect()
            ->route('tackle-reviews.show', $tackleReview)
            ->with('status', 'Review updated.');
    }

    public function destroy(TackleReview $tackleReview): RedirectResponse
    {
        $this->authorize('delete', $tackleReview);

        $tackleReview->load('photos');
        $tackleReview->photos->each->delete();
        $tackleReview->delete();

        return redirect()
            ->route('tackle-reviews.index')
            ->with('status', 'Review deleted.');
    }
}
