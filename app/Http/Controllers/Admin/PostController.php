<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{

    
    private $validation = [
        'title'         => 'required|string|min:1|max:150|unique:posts,title',
        'description'   => 'required',
        'order'         => 'required',

        'img_1'         => 'required|max:4200',
    ];
    private $validation1 = [
        'title'         => 'required|string|min:1|max:150',
        'description'   => 'required',
        'order'         => 'required',
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

        $order = $request->input('order');

        $filters = [
            'title'         => $title,
            'visible'       => $visible,
            'order'         => $order,      
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

        if($order){
            $posts = $query->orderBy('title',)->get();    
        }else{
            $posts = $query->orderBy('order', 'desc')->get();
        }        
        if ($archive == 1) {

            return view('admin.Posts.archived', compact('posts', 'filters'));
        }

        return view('admin.Posts.index', compact('posts', 'filters'));
        
    }

    public function index()
    {
        $posts    = Post::where('archived', false)->orderBy('order', 'desc')->get(); 
        return view('admin.Posts.index', compact('posts'));
    }

    public function create()
    {
        $categories    = Category::all(); 
        return view('admin.Posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        //$request->validate($this->validation);    
        dd($data);  
        
        $links = json_encode($data['videos']);

        $post = new Post();

        if (isset($data['img_1'])) {
            $img_1Path = Storage::put('public/uploads', $data['img_1']);
            $post->img_1 = $img_1Path;
        } 
        if (isset($data['img_2'])) {
            $img_2Path = Storage::put('public/uploads', $data['img_2']);
            $post->img_2 = $img_2Path;
        } 

        $post->title         = $data['title'];

        $post->description   = $data['description'];

        $post->order         = $data['order'];
        $post->place         = $data['place'];
        $post->date          = $data['date'];
        $post->links         = $links;
        
        $post->save();
        if(isset($data['categories'])){     
            foreach ($data['categories'] as $v) {
                array_push($categories, $v);
            }
            $post->categories()->sync($categories ?? []);  
        }   
        

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
        $links = json_encode($data['videos']);
        $post = Post::where('id', $id)->firstOrFail();
        if (isset($data['img_1'])) {
            $img_1Path = Storage::put('public/uploads', $data['img_1']);
            if ($post->img_1) {
                Storage::delete($post->img_1);
            }
            $post->img_1 = $img_1Path;
        }
        if (isset($data['img_2'])) {
            $img_2Path = Storage::put('public/uploads', $data['img_2']);
            if ($post->img_2) {
                Storage::delete($post->img_2);
            }
            $post->img_2 = $img_2Path;
        }

        $post->title         = $data['title'];

        $post->description   = $data['description'];

       
        $post->order         = $data['order'];
        $post->place         = $data['place'];
        $post->date          = $data['date'];
        $post->links         = $links;
        
        

      
        if(isset($data['categories'])){     
            foreach ($data['categories'] as $v) {
                array_push($categories, $v);
            }
            $post->categories()->sync($categories ?? []);  
        }   
        $post->update();
      
        return view('admin.Posts.show', compact('post'));
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return to_route('admin.posts.index')->with('delete_success', $post);
    }
}
