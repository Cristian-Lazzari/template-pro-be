<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{

    
    private $validation = [
        'title'         => 'required|string|min:1|max:150|unique:posts,title',
        'hashtag'       => 'nullable|string',
        'link'          => 'nullable|string',

        'description'   => 'required',
        'path'          => 'required',

        'image'         => 'required',
    ];
    private $validation1 = [
        'title'         => 'required|string|min:1|max:150',
        'hashtag'       => 'nullable|string',
        'link'          => 'nullable|string',

        'description'   => 'required',
        'path'          => 'required',
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
                $m = '"' . $p->title . '" e\' stato archiviato correttamente';
            } else{
                $m = '"' . $p->title . '" e\' stato ripristinato correttamente';
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
                $m = '"' . $p->title . '" e\' visibile ai tuoi clienti ';
            } else{
                $m = '"' . $p->title . '" non e\' visibile ai tuoi clienti';
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

    public function filter(Request $request){
        
        // FUNZIONE DI FILTRAGGIO INDEX
        
        $archive = $request->input('archive');
        $visible = $request->input('visible');
        $title = $request->input('title');
        $path = $request->input('path');
        $order = $request->input('order');
        $style = $request->input('style');
        $type = $request->input('type');
        $filters = [
            'title'         => $title,
            'path'          => $path,
            'visible'       => $visible,
            'type'          => $type,
            'order'         => $order,      
            'style'         => $style,    
        ];
        
        $query = Post::query();
        
        if ($archive == 1) {
            $query->where('archived', true);
        }else{
            $query->where('archived', false);
        }
        if ($title) {
            $query->where('title', 'like', '%' . $title . '%');
        } 
        if ($visible == 1) {
            $query->where('visible', '=', 1);
        } else if ($visible == 2) {
            $query->where('visible', '=', 0);
        }
        if ($path == 1) {
            $query->where('path', '=', 1);
        } else if ($path == 2) {
            $query->where('path', '=', 2);
        }
        if($type){
            $query->where('type', $type);
        }
        if($order){
            $posts = $query->orderBy('title',)->get();    
        }else{
            $posts = $query->orderBy('order', 'desc')->get();
        }        
        if ($archive == 1) {

            return view('admin.Posts.archived', compact('posts', 'filters'));
        }
        $news = Post::where('archived', false)->where('path', 1)->orderBy('order', 'desc')->get(); 
        $story = Post::where('archived', false)->where('path', 2)->orderBy('order', 'desc')->get(); 

        return view('admin.Posts.index', compact('posts', 'filters', 'story', 'news'));
        
    }

    public function index()
    {
        $posts = Post::where('archived', false)->orderBy('order', 'desc')->get(); 
        $news = Post::where('archived', false)->where('path', 1)->orderBy('order', 'desc')->get(); 
        $story = Post::where('archived', false)->where('path', 2)->orderBy('order', 'desc')->get(); 
        return view('admin.Posts.index', compact('posts', 'story', 'news'));
    }

    public function neworder(Request $request)
    {
        $ids = $request->input('new_order');
        $invertito = array_reverse($ids);
        $s= 0;
        foreach ($invertito as $id) {
            $post = Post::where('id', $id)->first();
            $post->order = $s; 
            $post->update(); 
            $s++;
        }
        
        //dd($ids);
        $m = 'Ordine aggiornato correttamente';
    
        return to_route('admin.posts.index')->with('order_success', $m);   
    }

    public function create()
    {
        return view('admin.Posts.create');
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
        
        $post->save();
      
        return view('admin.Posts.show', compact( 'post'));    
    }
    
    
    public function show($id)
    {
        $post = Post::where('id', $id)->firstOrFail();
        return view('admin.Posts.show', ['post' => $post]);      
    }
    
    public function edit($id)
    {
        $post = Post::where('id', $id)->firstOrFail();  
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
        $post->promo      = isset($data['promo']) ? true : false;
        
        $post->update();
      
        return view('admin.Posts.show', compact('post'));
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return to_route('admin.posts.index')->with('delete_success', $post);
    }
}
