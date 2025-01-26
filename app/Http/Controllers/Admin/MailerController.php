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
        'recipients'        => 'required',
        'object'            => 'required|string|min:1|max:120',
        'heading'           => 'required|string|min:1|max:120',
        'img_1'             => 'nulable|image|max:826',
        'img_2'             => 'nulable|image|max:826',
        'body'              => 'required|string|min:1|max:820',
        'ending'            => 'required|string|min:1|max:420',
        'sender'            => 'required|string|min:1|max:40',

    ];
    public function mailer()
    {
        return view('admin.mailer');   
    }
    public function send_mail(Request $request)
    {
        //$request->validate($this->validations);
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
        
        $old_mail = Setting::where('name', 'mail_list')->firstOrFail();
        if(!$old_mail){
            $new_set = [
                'name' => 'Prenotaione Tavoli',  
                'status' => 1,
                'property' => [
                    'last_mail_list' => json_encode($recipients),
                    'last_n_contact' => $n_contact
                ],
            ];
            Setting::create($new_set);
        }else{
            $old_mail->property = json_encode($recipients);
            $old_mail->update();
        }
        $m = 'Sono state correttamente inviate ' . $n_contact . ' email';
        return view('admin.mailer.create')->with('send_success', $m);   
    }
}
