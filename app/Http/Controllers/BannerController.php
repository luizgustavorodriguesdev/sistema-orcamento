<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Banner;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Banners/Index', [
            'banners' => Banner::orderBy('order')->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Banners/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'image' => 'required|image|max:3072',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->except('image');
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        $data['order'] = $request->input('order', 0) ?? 0;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');
            $data['image_path'] = $path;
        }

        Banner::create($data);

        return redirect()->route('banners.index')->with('success', 'Banner criado com sucesso.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banner $banner): Response
    {
        return Inertia::render('Banners/Edit', [
            'banner' => $banner,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:3072',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->except('image');
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        $data['order'] = $request->input('order', 0) ?? 0;

        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $path = $request->file('image')->store('banners', 'public');
            $data['image_path'] = $path;
        }

        $banner->update($data);

        return redirect()->route('banners.index')->with('success', 'Banner atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner): RedirectResponse
    {
        if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
            Storage::disk('public')->delete($banner->image_path);
        }
        $banner->delete();

        return redirect()->route('banners.index')->with('success', 'Banner excluído com sucesso.');
    }
}
