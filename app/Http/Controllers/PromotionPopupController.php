<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PromotionPopup;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class PromotionPopupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('PromotionPopups/Index', [
            'popups' => PromotionPopup::latest()->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('PromotionPopups/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'button_text' => 'nullable|string|max:255',
            'button_link' => 'nullable|string|max:255',
            'has_countdown' => 'nullable|boolean',
            'countdown_end' => 'nullable|date|required_if:has_countdown,true|required_if:has_countdown,1',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|max:3072',
        ]);

        $data = $request->except('image');
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        $data['has_countdown'] = $request->has('has_countdown') ? $request->boolean('has_countdown') : false;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('promotion_popups', 'public');
            $data['image_path'] = $path;
        }

        PromotionPopup::create($data);

        return redirect()->route('promotion-popups.index')->with('success', 'Popup promocional criado com sucesso.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PromotionPopup $promotionPopup): Response
    {
        return Inertia::render('PromotionPopups/Edit', [
            'popup' => $promotionPopup,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PromotionPopup $promotionPopup): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'button_text' => 'nullable|string|max:255',
            'button_link' => 'nullable|string|max:255',
            'has_countdown' => 'nullable|boolean',
            'countdown_end' => 'nullable|date|required_if:has_countdown,true|required_if:has_countdown,1',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|max:3072',
        ]);

        $data = $request->except('image');
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        $data['has_countdown'] = $request->has('has_countdown') ? $request->boolean('has_countdown') : false;

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($promotionPopup->image_path && Storage::disk('public')->exists($promotionPopup->image_path)) {
                Storage::disk('public')->delete($promotionPopup->image_path);
            }
            $path = $request->file('image')->store('promotion_popups', 'public');
            $data['image_path'] = $path;
        }

        $promotionPopup->update($data);

        return redirect()->route('promotion-popups.index')->with('success', 'Popup promocional atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PromotionPopup $promotionPopup): RedirectResponse
    {
        if ($promotionPopup->image_path && Storage::disk('public')->exists($promotionPopup->image_path)) {
            Storage::disk('public')->delete($promotionPopup->image_path);
        }
        $promotionPopup->delete();

        return redirect()->route('promotion-popups.index')->with('success', 'Popup promocional excluído com sucesso.');
    }
}
