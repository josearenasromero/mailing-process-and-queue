<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\SendMeetauthorEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class TemplateJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable, IsMonitored;

     
    
    public $email;
    public $temp_email_sub;
    public $temp_template;
    public $date;
    public $time;
    
  
    /**
     * Create a new job instance.
     */

    public function __construct($email,                                  
                                $temp_email_sub, 
                                $temp_template,
                                $date,
                                $time)    
    {
        $this->email = $email;  
        $this->temp_email_sub = $temp_email_sub;
        $this->temp_template = $temp_template;
        $this->date = $date;
        $this->time = $time;
        $this->onQueue('spotlight');
    }
   
    public $tries = 3;
 

    public function uniqueId(): string
    {
        return md5($this->email . $this->temp_email_sub);
    }

    public function initialMonitorData()
    {
        return ['Template' => $this->temp_email_sub,
                'Destination' => $this->email,
                'Date' => $this->date,
                'Time' => $this->time
                ];
    }
   
    public function handle(): void    
    { 
        Cache::forever($this->email . $this->temp_email_sub, 'Enviado');        
        $template["email_sub"] = $this->temp_email_sub;
        $template["template"] = $this->temp_template;

        $this->queueData([
            'Template' => $this->temp_email_sub,
            'Destination' => $this->email,
            'Date' => $this->date,
            'Time' => $this->time
        ]);
       
        // Log::info('The processed ' . $template["email_sub"]. ' template.');      

        // echo 'The processed ' . $template["email_sub"] . ' template.'."\n";      
                   
                
            $htmlContent ='<html>
                <head>
                <title>AXP Spotlight</title>
                </head>
                <body>
                <div class="container" style="width: 100%;margin:0 auto;">';
            $emailsubj = $template["email_sub"];
            $template_temp = $template["template"];            


            $bookbind = 1;
            $htmlContent .='<div class="col-md-12 " style="width: 100%;min-height: 1px;padding-left: 15px;padding-right: 15px;position: relative;margin:0 auto;">
            <div class="cat-book row" style="display: inline-block;">
            <div style="width: 100%;margin:0 auto;color:#000!important">'.$template_temp.'</div>';
            $htmlContent .='
            </div>
            </div>';

            $htmlContent .= '</div>    
                </body>
                </html>';

            $date1=date("l");
            $subject = $emailsubj;
            $to = $this->email;
            $htmlContent2='<div style="width:100%;margin:0 auto;background-color:#3490bf;padding:10px 0px 14px 0;margin-bottom:20px;"><span style="font-family: sans-serif;padding-top:0px;padding-left: 150px; font-size: 23px; color: rgb(255, 255, 255); width: 200px; float: left; font-family: sans-serif;"><a href="https://authorsxp.com" style="text-decoration:none;color:#fff">AXP Books</a></span><span style="width:100%;text-align:right;margin-left:30%;font-size:18px;color:#fff;font-family: sans-serif;"><a href="http://promo.authorsxp.com" style="text-decoration:none;color:#fff;font-size:13px" target="_blank">View Online</a> | <a href="https://authorsxp.com/refer" style="text-decoration:none;color:#fff;font-size:13px" target="_blank">Refer a Friend</a> | <a href="http://promo.authorsxp.com/chosecategory.php?email='. $to .'" style="text-decoration:none;color:#fff;font-size:13px" target="_blank">My Account</a></span></div><div align="center"><em>Not interested in these genres? Click on MY ACCOUNT above, login with your email and EDIT PREFERENCES.</em></div><p>';
            $htmlContent11113='<div class="col-md-12 " style="width: 100%;text-align:center;background-color:#eeeeee;padding:10px 0;margin:20px 0;font-weight:normal;font-size:12px;position: relative;color:#000 !important"><p style="color:#000 !important">Prices may change without notice or be dependent upon region or retailer  - please verify that the deal is still available before downloading.</p><p style="color:#000 !important">AuthorsXP by Vansant Creations - 328 River Edge Rd. Jupiter, FL 33477 - USA</p></div>';

            $htmlContent1='
                <div class="col-md-12" style="width: 100%; padding-left: 15px; padding-right: 15px; position: relative;">
                    <div style="text-decoration: none; font-weight: 700; margin-bottom: 8px; font-size:15px;
                        margin-right: 4px; padding-bottom: 5px; padding-left: 5px; padding-right: 5px;
                        padding-top:0px;margin-left:22%; position:absolute;bottom:0px">
                        <a class="btn btn-primary btn-xs"
                            href="http://promo.authorsxp.com/chosecategory.php?email='. $to .'">
                            Change your preferred categories</a> |
                        <a href="http://promo.authorsxp.com/unsubscribe.php?email='. $to .'"
                            target="_blank">Unsubscribe</a>
                    </div>
                </div>
                <hr style="border-bottom: 0px solid #ccc; width: 100%; margin: 0 auto; margin-bottom: 15px; margin-top: 15px">
            ';
            $htmlContent12=$htmlContent2.$htmlContent.$htmlContent11113.$htmlContent1;

            $from = 'amy@authorsxp.com';                 
            $name = 'Authors Cross Promotion';
            $subj = $subject;
            $msg = $htmlContent12;             
                    
                Mail::to($to)->send(new SendMeetauthorEmail(
                    $to, 
                    $from,  
                    $name,  
                    $subj,
                    $msg 
                ));
                      
                echo "EMAIL SENT"."\n";
        
    }
}
