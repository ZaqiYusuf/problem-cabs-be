<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'categories' => Category::all(),
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $request->validate([
                'item' => 'required',
                'type' => 'required'
            ], [
                'item.required' => 'Please enter item',
                'type.required' => 'Please enter type'
            ]);
    
            Category::create($request->all());
    
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'details' => $e->getMessage()
            ], 409);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $categories = Category::find($id);
        
        if (!$categories) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $updated = $categories->update([
            'item' => $request->item,
            'type' => $request->type
        ]);

        try {
            $updated = $categories->update([
                'item' => $request->item,
                'type' => $request->type
            ]);

            return response()->json([
                'success' => $updated,
                'message' => 'Category updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'details' => $e->getMessage()
            ], 409);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $categories = Category::find($id);

        if ($categories) {
            $categories->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
    }
}
