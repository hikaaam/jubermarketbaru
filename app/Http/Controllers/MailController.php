<?php

namespace App\Http\Controllers;

use App\Mail\MailNoReplyAdmin;
use App\Mail\MailSendAdmin;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function sendMailAdmin(Request $request)
    {
        $request = json_decode($request->payload, true);
        try {
            helper::validateArray($request, [
                "nama:string",
                "email:string",
                "isi:string"
            ]);
            $isEmailValid = helper::validateEmail($request['email']);
            if (!$isEmailValid) {
                throw new Error("Email tidak valid");
            }
            Mail::to($request['email'])->send(new MailSendAdmin($request));
            Mail::to("admin@juber.co.id")->send(new MailNoReplyAdmin($request));
            return helper::resp(true, "update", "Email anda berhasil dikirim", []);
        } catch (\Throwable $th) {
            return helper::resp(false, "update", $th->getMessage(), []);
        }
    }
}
