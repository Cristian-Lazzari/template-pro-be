<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{

    
    private $validation = [
        'title'         => 'required|string|min:1|max:100|unique:posts,title',
        'hashtag'       => 'string',
        'description'   => 'required',
        'path'          => 'required',
        'image'         => 'required',
        'link'          => 'nullable|string'
    ];
    private $validation1 = [
        'title'         => 'required|string|min:1|max:100',
        'hashtag'       => 'string',
        'description'   => 'required',
        'path'          => 'required',
        'link'          => 'nullable|string'
    ];


   
    
    public function status(Request $request){
        $a = $request->a ;
        $v = $request->v ;
        $archive = $request->archive ;
        
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
                return to_route('admin.Posts.archived', compact('posts'))->with('success', $m);
            } else{
                $posts = Post::where('archived', false)->get();
                return to_route('admin.Posts.index', compact('posts'))->with('success', $m);
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
                return to_route('admin.Posts.archived', compact('posts',))->with('success', $m);
            } else{
                $posts = Post::where('archived', false)->get();
                return to_route('admin.Posts.index', compact('posts',))->with('success', $m);
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
            $query->where('path', '=', 0);
        }
        if($type){
            $query->where('type', $type);
        }
        if($order){
            $posts = $query->orderBy('order')->get();
        }else{
            $posts = $query->orderBy('updated_at', 'desc')->get();    
        }        
        if ($archive == 1) {

            return view('admin.Posts.archived', compact('posts', 'filters'));
        }

        return view('admin.Posts.index', compact('posts', 'filters'));
        
    }

    public function index()
    {
        $posts    = Post::where('archived', false)->get(); 
        return view('admin.Posts.index', compact('posts'));
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
        $post->order         = $data['order'];
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
        $post->order         = $data['order'];
        $post->link          = $data['link'];
        
        $post->update();
      
        return view('admin.Posts.show', compact('post'));
    }

    public function destroy(Post $post)
    {
        $post->products()->detach();
        $post->delete();
        return to_route('admin.Posts.index')->with('delete_success', $post);
    }
}
