<?php

namespace App\Http\Controllers\Articles;


use Illuminate\Http\Request;
use App\Models\Articles\Article;
use App\Http\Controllers\Controller;
use App\Models\ArticleGroup\ArticleGroup;
use App\Models\Articles\ArticleCategory;
use App\Models\Articles\Category;
use App\Models\Groups\Group;
use App\Models\GroupUser\GroupUser;
use App\Models\Users\User;
use App\Notifications\ArticleCreated;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index()
    {
        if (Gate::allows('aa_staff2')) {
            $article = Article::get();
            //$article->load('group');
            return response()->json($article);
        }
    }

    public function checkSlug($slug)
    {
        $article = Article::where('slug', '=', $slug)->first();
        return response()->json($article);
    }


    public function trash()
    {
        $article = Article::join('users', 'el_articles.user_id', 'users.id')
            ->onlyTrashed()
            ->get([
                'el_articles.id', 'el_articles.title', 'el_articles.slug', 'el_articles.content',
                'el_articles.date', 'el_articles.status', 'users.name', 'el_articles.user_id',
            ]);

        return response()->json($article);
    }
    public function slugCreate(Request $request)
    {


        $title = $request->title;
        $slug = str()->slug($title);
        $allSlugs = Article::select('slug')->where('slug', 'like', $slug . '%')
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

    public function store()

    {

        $form = request()->validate([

            'title' => 'required',
            'slug' => 'required|unique:el_articles,slug',
            'content' => 'required',
            'gname' => 'nullable',
            'status' => (Gate::allows('aa_mgmt')) ? 'max:1|string|regex:/(^([PDR])$)/u' : 'max:1|string|regex:/(^([R])$)/u',
            'date' => 'required',
            'category' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $gname = $form['gname'];
            $form['ingroup'] = $gname == TRUE ? '1' : '';
            $form['user_id'] = Auth()->user()->id;


            if (Gate::allows('aa_staff1')) {
                $article = Article::create($form);

                foreach ((array)$gname as $gn) {
                    $group = Group::where('name', $gn)->first();
                    ArticleGroup::create([
                        'article_id' => $article->id,
                        'group_id' => $group->id,
                    ]);
                }
                foreach ((array)$form['category'] as $cat) {
                    $cate = Category::where('name', $cat)->first();

                    ArticleCategory::create([
                        'article_id' => $article->id,
                        'category_id' => $cate->id,
                    ]);
                }

                if ($form['status'] === 'P' && $gname !== null) {
                    $allUsers = [];
                    foreach ((array)$gname as $gn) {

                        $group = Group::where('name', $gn)->first();
                        //return $group;
                        $allUsers[] = User::join('group_user', 'group_user.user_id', 'users.id')
                            ->where('group_id', $group->id)->get(['users.name', 'users.email'])->toArray();
                    }

                    $userlist = array_merge(...$allUsers);
                    $u_list = array_unique($userlist, SORT_REGULAR);
                    if ($u_list !== null) {

                        foreach ($u_list as $ul) {
                            $data = [
                                'title' => $form['title'],
                                'name' => $ul['name'],
                            ];

                            Notification::route('mail', $ul['email'])->notify(new ArticleCreated($data));
                        }
                    }
                }
            }



            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Article Created Successfully'

            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()

            ], 500);
        }
    }


    public function edit($id)
    {

        $article = Article::findOrFail($id);

        $article->load('group');
        $article->load('category');
        if ($article) {
            return response()->json([
                'status' => true,
                'article' => $article

            ], 200);
        }
    }

    public function update($id)
    {
        $form = request()->validate([
            'title' => 'string',
            'content' => 'string',
            'gname' => 'nullable',
            'status' => (Gate::allows('aa_mgmt')) ? 'max:1|string|regex:/(^([PDR])$)/u' : 'max:1|string|regex:/(^([R])$)/u',
            'date' => 'string',
            'category' => 'array',
        ]);
        try {
            DB::beginTransaction();
            $article = Article::findOrFail($id);
            $article->update($form);

            if (isset($form['gname'])) {
                ArticleGroup::where('article_id', '=', $id)->delete();
                $gname = $form['gname'];
                foreach ((array)$gname as $gn) {
                    $group = Group::where('name', $gn)->first();
                    ArticleGroup::create([
                        'article_id' => $article->id,
                        'group_id' => $group->id,
                    ]);
                }
            }
            if (isset($form['category'])) {
                ArticleCategory::where('article_id', '=', $id)->delete();
                foreach ((array)$form['category'] as $cat) {
                    $cate = Category::where('name', $cat)->first();
                    ArticleCategory::create([
                        'article_id' => $article->id,
                        'category_id' => $cate->id,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Article updated Successfully'

            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()

            ], 500);
        }
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return response()->json([
            'status' => true,
            'message' => 'Article Move to Trash'

        ], 200);
    }
    public function restore($id)
    {
        $article = Article::withTrashed()->findOrFail($id);
        if (Gate::allows('aa_staff1')) {
            $article->restore();
        }
        return response()->json([
            'status' => true,
            'message' => 'Article restored Successfully'

        ], 200);
    }
    public function forceDelete($id)
    {
        $article = Article::withTrashed()->findOrFail($id);

        ArticleGroup::where('article_id', '=', $id)->delete();
        if (Gate::allows('aa_mgmt')) {

            $article->forceDelete();
        }
        return response()->json([
            'status' => true,
            'message' => 'Article Deleted Successfully'

        ], 200);
    }

    public function approveArticle($id)
    {
        $article = Article::with(['group' => function ($gr) {
            $gr->select('name');
        }])->findOrFail($id);
        $groups = $article->group;
        if ($article) {
            $article->status = 'P';
            if (Gate::allows('aa_mgmt')) {
                $article->save();
                //$data = $article->title;
                if ($groups !== null) {
                    $allUsers = [];
                    foreach ($groups as $gn) {
                        $group = Group::where('name', $gn->name)->first();
                        $allUsers[] = User::join('group_user', 'group_user.user_id', 'users.id')
                            ->where('group_id', $group->id)->get(['users.name', 'users.email'])->toArray();
                    }
                    $userlist = array_merge(...$allUsers);
                    $u_list = array_unique($userlist, SORT_REGULAR);
                    if ($u_list !== null) {

                        foreach ($u_list as $ul) {
                            $data = [
                                'title' => $article->title,
                                'name' => $ul['name'],
                            ];

                            Notification::route('mail', $ul['email'])->notify(new ArticleCreated($data));
                        }
                    }
                }
            } else {
                return response([
                    'message' => 'you are not allow to approve'
                ], 403);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Approved Successfully'

        ], 200);
    }


    ########   For User  ###########

    public function articlesIndex(Request $request)
    {
        $request->validate([
            'slug' => 'nullable',
        ]);
        $slug = $request->slug;
        $user = auth('sanctum')->user();
        $groups = [];

        if ($user) {
            $user = User::where('id', $user->id)
                ->with(['groups' => function ($q) {
                    $q->select(['el_groups.id']);
                }])
                ->with(['all_access_roles' => function ($q) {
                    $q->select(['roles.id', 'roles.name']);
                }])
                ->with(['instructor_roles' => function ($q) {
                    $q->select(['roles.id', 'roles.name']);
                }])
                ->first();

            foreach ($user->groups as $g) {
                array_push($groups, $g->id);
            }
        }

        $category = null;
        if ($slug) {
            $category = Category::where('slug', $slug)->firstOrFail();
        }

        $articles = Article::select('*')
            ->with(['categories' => function ($q) {
                $q->select(['categories.id', 'categories.slug', 'categories.name']);
            }])

            ->when($category, function ($q) use ($category) {
                $q->whereHas('categories', function ($q) use ($category) {
                    $q->where('categories.id', $category->id);
                });
            })

            ->when($user, function ($q) use ($user, $groups) {
                # user is signed in.

                $q->when($user->all_access_roles->count() == 0, function ($q) use ($user, $groups) {
                    # user does not have all access, perform filtering

                    $q->when($user->instructor_roles->count() > 0, function ($q) use ($user) {
                        # user is instructor. Show only article owned
                        $q->where('user_id', $user->id);
                    })
                        ->when($user->instructor_roles->count() == 0, function ($q) use ($user, $groups) {
                            # user is not instructor. Show only subscribed groups
                            $q->whereHas('groups', function ($q) use ($groups) {
                                $q->whereIn('el_groups.id', $groups);
                            });
                        });
                });
            })
            ->when(!$user, function ($q) {
                # user is not signed in. show only articles not in group
                $q->where('ingroup', 0);
            })
            ->where('status', '=', 'P')
            ->orderByDesc('date')
            ->get()->toArray();

        # without group article when user loged In
        $art = [];
        if (auth('sanctum')->user()) {
            $art = Article::select('*')
                ->with(['categories' => function ($q) {
                    $q->select(['categories.id', 'categories.slug', 'categories.name']);
                }])
                ->when($category, function ($q) use ($category) {
                    $q->whereHas('categories', function ($q) use ($category) {
                        $q->where('categories.id', $category->id);
                    });
                })
                ->where('ingroup', 0)
                ->where('status', '=', 'P')
                ->orderByDesc('date')
                ->get()->toarray();
        }
        $article = array_merge($articles, $art);
        return response()->json($article);
        //return $art;
        //return $articles;
    }
    public function show($slug)
    {
        $article = Article::select('*')
            ->with(['user' => function ($q) {
                $q->select(['users.id', 'users.name']);
            }])
            ->with(['categories' => function ($q) {
                $q->select(['categories.id', 'categories.slug', 'categories.name']);
            }])
            ->with(['groups' => function ($q) {
                $q->select(['el_groups.id', 'el_groups.name']);
            }])
            ->where('slug', '=', $slug)
            ->firstOrFail();

        return response()->json($article);
    }
}
