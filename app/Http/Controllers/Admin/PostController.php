<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{


    private $validation = [
        'title'         => 'required|string|min:1|max:150|unique:posts,title',
        'hashtag'       => 'nullable|string',
        'link'          => 'nullable|string',
        'link_label'    => 'nullable|string|max:60',

        'description'   => 'required',
        'path'          => 'required',

        'image'         => 'required|image|max:1024',
        'images'        => 'nullable|array',
        'images.*'      => 'image|max:1024',
    ];
    private $validation1 = [
        'title'         => 'required|string|min:1|max:150',
        'hashtag'       => 'nullable|string',
        'link'          => 'nullable|string',
        'link_label'    => 'nullable|string|max:60',

        'description'   => 'required',
        'path'          => 'required',

        'image'         => 'required|image|max:1024',
        'images'        => 'nullable|array',
        'images.*'      => 'image|max:1024',
    ];


   
    
    public function status(Request $request){
        $a = $request->a ;
        $v = $request->v ;
        $archive = $request->archive ; //serve per capire dove reindirizzare //TO-DO sostituire qui e su product con back()
        
        ///se uguale a 1 archivio il prodotto
        if($a == 1){
            $p = Post::where('id', $request['id'])->firstOrFail();
            $p->archived = !$p->archived;
            $p->update();
            if ($p->archived) {
                $m = __('admin.controllers.post.archived_named', ['name' => $p->title]);
            } else{
                $m = __('admin.controllers.post.restored_named', ['name' => $p->title]);
            }
            if ($archive == 1) {
                $posts = Post::where('archived', true)->get();
                return to_route('admin.posts.archived', compact('posts'))->with('success', $m);
            } else{
                $posts = Post::where('archived', false)->get();
                return to_route('admin.posts.index', compact('posts'))->with('success', $m);
            }
            
            
        } 
        if($v == 1){
            $p = Post::where('id', $request['id'])->firstOrFail();
            $p->visible = !$p->visible;
            $p->update();
            if ($p->visible) {
                $m = __('admin.controllers.post.visible_named', ['name' => $p->title]);
            } else{
                $m = __('admin.controllers.post.hidden_named', ['name' => $p->title]);
            }
            if ($archive == 1) {
                $posts = Post::where('archived', true)->get();
                return to_route('admin.posts.archived', compact('posts',))->with('success', $m);
            } else{
                $posts = Post::where('archived', false)->get();
                return to_route('admin.posts.index', compact('posts',))->with('success', $m);
            }
        } 
    }

    public function archived(Request $request){
        $posts   = Post::where('archived', true)->get();
        
        
        return view('admin.Posts.archived', compact('posts'));
    }


    public function index()
    {
        $posts = Post::query()
            ->where('archived', false)
            ->orderBy('order', 'desc')
            ->select(['id', 'title', 'path', 'link', 'image', 'visible'])
            ->paginate(60);

        $news = Post::where('archived', false)->where('path', 1)->orderBy('order', 'desc')->get(); 
        $story = Post::where('archived', false)->where('path', 2)->orderBy('order', 'desc')->get(); 
        return view('admin.Posts.index', compact('posts', 'story', 'news'));
    }

    public function search(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $type = $request->query('type', 'all');
        $sort = $request->query('sort', 'recent') === 'alpha' ? 'alpha' : 'recent';

        $query = Post::query()->where('archived', false);

        if ($search !== '') {
            $query->where(function ($nested) use ($search) {
                $nested->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('hashtag', 'like', "%{$search}%");
            });
        }

        if ($type === 'story') {
            $query->where('path', 2);
        }
        if ($type === 'news') {
            $query->where('path', 1);
        }

        if ($sort === 'alpha') {
            $query->orderBy('title', 'asc');
        } else {
            $query->orderBy('order', 'desc');
        }

        $posts = $query
            ->limit(400)
            ->select(['id', 'title', 'path', 'link', 'image', 'visible'])
            ->get();

        return response()->json([
            'html' => view('admin.Posts.partials.index_cards', compact('posts'))->render(),
        ]);
    }

    public function quickView(Post $post)
    {
        return view('admin.Posts.partials.quick_view_modal_body', compact('post'));
    }

    public function neworder(Request $request)
    {
        $ids = array_values(array_filter((array) $request->input('new_order')));

        if ($ids === []) {
            return to_route('admin.posts.index')->with('order_success', __('admin.controllers.post.no_reorder_items'));
        }

        $invertito = array_reverse($ids);
        $s= 0;
        foreach ($invertito as $id) {
            $post = Post::find($id);

            if (!$post) {
                continue;
            }

            $post->order = $s; 
            $post->update(); 
            $s++;
        }
        
        //dd($ids);
        $m = __('admin.controllers.post.order_updated');
    
        return to_route('admin.posts.index')->with('order_success', $m);   
    }

    public function create()
    {
        return view('admin.Posts.create', [
            'post' => new Post(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $request->validate($this->validation);      
        
        $post = new Post();
        if (isset($data['image'])) {
            $imagePath = Storage::put('public/uploads', $data['image']);
            $post->image = $imagePath;
        } 

        $post->title         = $data['title'];
        $post->hashtag       = $data['hashtag'];
        $post->description   = $data['description'];
        $post->path          = $data['path'];
        $post->order         = Post::max('order') + 1;
        $post->link          = $data['link'];
        $post->link_label    = $data['link_label'] ?? null;

        $post->save();

        $this->storeGalleryImages($request, $post);

        return to_route('admin.posts.index');
    }

    /**
     * Salva le foto aggiuntive (galleria) caricate per un post.
     */
    private function storeGalleryImages(Request $request, Post $post)
    {
        if (!$request->hasFile('images')) {
            return;
        }

        $order = (int) $post->images()->max('order');

        foreach ($request->file('images') as $file) {
            if (!$file) {
                continue;
            }

            $order++;
            $post->images()->create([
                'image' => Storage::put('public/uploads', $file),
                'order' => $order,
            ]);
        }
    }
    
    
    public function show($id)
    {
        $post = Post::where('id', $id)->firstOrFail();
        return view('admin.Posts.show', ['post' => $post]);      
    }
    
    public function edit($id)
    {
        $post = Post::with('images')->where('id', $id)->firstOrFail();
        return view('admin.Posts.edit', compact( 'post'));
    }
    
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $request->validate($this->validation1); 
        $post = Post::where('id', $id)->firstOrFail();
        if (isset($data['image'])) {
            $imagePath = Storage::put('public/uploads', $data['image']);
            if ($post->image) {
                Storage::delete($post->image);
            }
            $post->image = $imagePath;
        }
        $post->title         = $data['title'];
        $post->hashtag       = $data['hashtag'];
        $post->description   = $data['description'];
        $post->path          = $data['path'];
        $post->link          = $data['link'];
        $post->link_label    = $data['link_label'] ?? null;
        $post->promo      = isset($data['promo']) ? true : false;

        $post->update();

        // Rimozione delle foto della galleria selezionate
        if (!empty($data['delete_images'])) {
            $toDelete = $post->images()->whereIn('id', (array) $data['delete_images'])->get();
            foreach ($toDelete as $image) {
                if ($image->image) {
                    Storage::delete($image->image);
                }
                $image->delete();
            }
        }

        // Aggiunta delle nuove foto caricate
        $this->storeGalleryImages($request, $post);

        return to_route('admin.posts.index');;
    }

    public function destroy(Post $post)
    {
        foreach ($post->images as $image) {
            if ($image->image) {
                Storage::delete($image->image);
            }
        }

        $post->delete();
        return to_route('admin.posts.index')->with('delete_success', $post);
    }
}
