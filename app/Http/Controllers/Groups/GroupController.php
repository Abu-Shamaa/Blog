<?php

namespace App\Http\Controllers\Groups;

use App\Http\Controllers\Controller;
use App\Models\ArticleGroup\ArticleGroup;
use App\Models\Groups\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    public function gindex()
    {
        $group = Group::all();
        return response()->json($group);
    }

    public function gstore(Request $request)
    {


        $form = request()->validate([
            //'name' => 'required',
            'name' => 'required|unique:el_groups,name',
        ]);

        Group::create($form);

        return response()->json([
            'status' => true,
            'message' => 'Group Created Successfully'

        ], 200);
    }

    public function gedit($id)
    {
        $group = Group::findOrFail($id);
        if ($group) {
            return response()->json([
                'status' => true,
                'group' => $group

            ], 200);
        }
    }

    public function gupdate(Request $request, $id)
    {
        $form = request()->validate([
            'name' => 'string|unique:el_groups,name',
        ]);
        $group = Group::findOrFail($id);
        $group->update($form);


        return response()->json([
            'status' => true,
            'message' => 'Group updated Successfully'

        ], 200);
    }

    public function gdestroy($id)
    {
        $group = Group::findOrFail($id);
        ArticleGroup::where('group_id', '=', $id)->delete();
        $group->delete();

        return response()->json([
            'status' => true,
            'message' => 'Group deleted Successfully'

        ], 200);
    }
}
