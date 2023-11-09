<?php

namespace App\Http\Controllers\Articles;

use App\Http\Controllers\Controller;
use App\Models\Articles\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function catIndex()
    {
        $category = Category::all();
        return response()->json($category);
    }
    public function checkSlug($slug)
    {
        $category = Category::where('slug', '=', $slug)->first();
        return response()->json($category);
    }
    public function slugCreate(Request $request)
    {


        $name = $request->name;
        $slug = str()->slug($name);
        $allSlugs = Category::select('slug')->where('slug', 'like', $slug . '%')
            ->get();
        if (!$allSlugs->contains('slug', $slug)) {
            return response()->json($slug);
        }

        $i = 1;
        $is_contain = true;
        do {
            $newSlug = $slug . '-' . $i;
            if (!$allSlugs->contains('slug', $newSlug)) {
                $is_contain = false;
                return response()->json($newSlug);
            }
            $i++;
        } while ($is_contain);
    }
    public function catStore()
    {


        $form = request()->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'description' => 'nullable',
        ]);

        Category::create($form);

        return response()->json([
            'status' => true,
            'message' => 'Category Created Successfully'

        ], 200);
    }

    public function catShow($id)
    {
        $category = Category::findOrFail($id);
        if ($category) {
            return response()->json([
                'status' => true,
                'category' => $category

            ], 200);
        }
    }

    public function catUpdate($id)
    {
        $form = request()->validate([
            'name' => 'string',
            'description' => 'nullable',
        ]);
        $category = Category::findOrFail($id);
        $category->update($form);


        return response()->json([
            'status' => true,
            'message' => 'Category updated Successfully'

        ], 200);
    }

    public function catDestroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'status' => true,
            'message' => 'Category deleted Successfully'

        ], 200);
    }
}
