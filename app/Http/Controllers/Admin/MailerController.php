<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Mail\BuildableMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class MailerController extends Controller
{
    private $validations = [
        'recipients'        => 'required|min:1',
        'object'            => 'required|string|min:1|max:120',
        'heading'           => 'required|string|min:1|max:120',
        'img_1'             => 'nullable|image|max:826',
        'img_2'             => 'nullable|image|max:826',
        'body'              => 'required|string|min:1|max:820',
        'ending'            => 'required|string|min:1|max:420',
        'sender'            => 'required|string|min:1|max:40',
    ];
    
    public function mailer()
    {

        $last_mail_list = 0;
        $last_n_contact = 0;
        
        //$old_mail = Setting::where('name', 'email_marketing')->first();
        $old_mail = Setting::where('name', 'email_marketing')->first();
        //dd($old_mail);
        if($old_mail == null){
            return view('admin.mailer', compact('last_mail_list', 'last_n_contact'));   
        }else{
            $prop = json_decode($old_mail->property);
            $last_mail_list = $prop->last_mail_list;
            $last_n_contact = $prop->last_n_contact;
            
            return view('admin.mailer', compact('last_mail_list', 'last_n_contact'));   
        }
    }
    public function send_mail(Request $request)
    {
        $request->validate($this->validations);
        $data = $request->all();
        ///dd($data);

        $recipients = $data['recipients'];
        $n_contact = count($recipients);

        if (isset($data['img_1'])) {
            $img_1_path = Storage::put('public/uploads', $data['img_1']);
        }else{
            $img_1_path = NULL;
        } 

        if (isset($data['img_2'])) {
            $img_2_path = Storage::put('public/uploads', $data['img_2']);
        }else{
            $img_2_path = NULL;
        } 
        
        $contentMail = [
            'object' => $data['object'],
            'heading' => $data['heading'],
            'body' => explode("/*/", $data['body']),
            'ending' => $data['ending'],
            'sender' => $data['sender'],
            'img_1' => $img_1_path,
            'img_2' => $img_2_path,
        ];


        foreach ($recipients as $c ) {   
            $mail = new BuildableMail($contentMail);
            Mail::to($c)->send($mail);
        }
        
        $old_mail = Setting::where('name', 'email_marketing')->first();
        if($old_mail == null){
            $new_set = [
                'name' => 'email_marketing',  
                'status' => 1,
                'property' => [
                    'last_mail_list' => $recipients,
                    'last_n_contact' => $n_contact
                ],
            ];
            $new_set['property'] = json_encode($new_set['property']);
            Setting::create($new_set);
        }else{
            $new_prop = json_decode($old_mail->property);
            $new_prop->last_mail_list = $recipients;
            
            $old_mail->property = json_encode($new_prop);
            $old_mail->update();
        }
        $m = 'Sono state correttamente inviate ' . $n_contact . ' email';
        return to_route('admin.mailer.create')->with('send_success', $m);   
    }
}
