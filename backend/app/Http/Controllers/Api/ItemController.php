<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Item::latest()->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:50', 'unique:items,sku'],
            'quantity' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $item = Item::create($data);

        return response()->json(['data' => $item], 201);
    }

    public function show(Item $item)
    {
        return response()->json(['data' => $item]);
    }

    public function update(Request $request, Item $item)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sku' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('items', 'sku')->ignore($item->id)],
            'quantity' => ['sometimes', 'required', 'integer', 'min:0'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $item->update($data);

        return response()->json(['data' => $item]);
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return response()->json(['message' => 'Item berhasil dihapus.']);
    }
}