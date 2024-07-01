<?php

namespace App\Jobs;

use App\Helpers\FunctionsHelper;
use App\Mail\SendDailyDealEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class DDTemplateJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;

    public $email;
    public $dd_array;
    public $dd_categories;
    public $categories;
    public $queue_dd_data_templates;
    public $current_date;
    public $array_field_values;

    public function __construct($email, $dd_array,$dd_categories, $categories, $queue_dd_data_templates, $current_date,$array_field_values)
    {
        $this->onQueue('dailydeal');
        $this->email = $email;      
        $this->dd_array = $dd_array;
        $this->dd_categories = $dd_categories;
        $this->categories = $categories;     
        $this->queue_dd_data_templates = $queue_dd_data_templates;        
        $this->current_date = $current_date;
        $this->array_field_values = $array_field_values;
    }

    public $tries = 3; 

    public function uniqueId(): string
    {                   
        return md5('DD_' . $this->email . '_' . $this->current_date);
    }

    public function initialMonitorData()
    {
        return $this->queue_dd_data_templates;
    }
    
    public function handle(): void
    {
        Cache::forever('DD_' . $this->email . '_' . $this->current_date, 'Enviado');
        $this->queueData(
            $this->queue_dd_data_templates
        ); 
        $email = $this->email;

        $functions = new FunctionsHelper();
        // $htmlContent ='<html>
		// 			   <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		// 			   <title>Welcome to AuthorXP</title>
		// 			   </head>
		// 			   <body>
		// 			   <div class="container" style="width: 90%;margin:0 auto;">';
        $htmlContentAcum ='<html>
        <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Welcome to AuthorXP</title>
        </head>
        <body>
        <div class="container" style="width: 90%;margin:0 auto;">';
        
        $addContentBookDay ='<div style="width: 100%;text-align: center;">';
        $hmtlContentBookDay ='';                          
        $has_books = false;     
        $addContentBookDay .= "<div>"; 
        $acum = 0;
        foreach ($this->dd_array as $daily_deal) {            
            $hmtlContentBookDay = '';
            $htmlContent=''; 
            $vbookid1 = $daily_deal['odd_id'];
            $vbookid = $functions->encrypt_decrypt('encrypt', $vbookid1);

            $vdailydealscat = $this->dd_categories[$vbookid1];

            if (count($vdailydealscat) < 1) continue;

            $do_or = $daily_deal['do_or'];

            $cat_flag = 0;
            foreach ($vdailydealscat as $cat_name) {
                if (in_array($cat_name, $this->categories)) $cat_flag++;
            }

            if ($do_or) {
                if ($cat_flag < 1) continue;                
            } else {
                if ($cat_flag != count($vdailydealscat)) continue;
            }           
            
            
            $has_books = true;
            $usual_price = $daily_deal["usual_price"];
            $promo_price = $daily_deal["promotion_price"];

            if (!in_array($promo_price, ['FREE', 'Free', 'New Release', 'FREE on Kindle Unlimited', 'Special'])) {
                $promo_price = '$' . $promo_price;
            }

            if (!in_array($usual_price, ['FREE', 'Free', 'New Release'])) {
                $tdec = 'line-through';
                $fontw = 'normal';
                $fcolor = '#969696';
            } else {
                $tdec = 'unset';
                $fontw = 'bold';
                $fcolor = '#333';

                $usual_price = '$' . $usual_price;
            }

            if ($usual_price == 'New Release' || $promo_price == 'New Release') {
                $tdec = 'unset';
            } else {
                $tdec = 'line-through';
            }

            $width = '45%';
            if(count($this->array_field_values) == 1){                
              $width = '100%';
            }

            $vimgnew12=$daily_deal["cover_image"];
            $vimgnew121=str_replace(" ", "%20", $vimgnew12);           
            
            
            $htmlContent .='<div class="col-md-12 " style="width: 100%;min-height: 1px;padding-left: 15px;padding-right: 15px;position: relative;margin:0 auto;">
								<div class="cat-book row" style="display: inline-block;">';
            
            $hmtlContentBookDay.='<div class="col-md-12" style="width: 100%;max-width: '.$width.';min-height: 1px;padding-left: 15px;padding-right: 15px;position: relative;border: solid;border-width: 1px;display: table-cell;justify-content: center;align-items: center; margin-top: 10px; margin-bottom: 10px;text-align: left;vertical-align: middle;">
                                <div class="cat-book row" style="display: flex;">';
                                                            
            if ($daily_deal['feature']!='0') {
                
                $htmlContent .='<h4 style="text-align:center;color:red;font-size:18px;margin-bottom:20px;">Featured Book!</h4>';     
                         
                $hmtlContentBookDay.= '<h4 style="text-align:center;color:red;font-size:18px;margin-bottom:20px;">Featured Book!</h4>';
            }
            
            $htmlContent .='<div class="col-sm-3 hidden-xs" style="width: 190px;float: left;">
								<div class="cat-book-cover" style="cursor:default;">';
            
            $hmtlContentBookDay.= '<div class="col-sm-3 hidden-xs" style="width: 190px;float: left;margin: auto;">
								<div class="cat-book-cover" style="cursor:default;">';
            if ($vimgnew121 != '') {
                
                $htmlContent .='<a href="http://promo.authorsxp.com/" target="_blank" ><img  class=" img-responsive" src="'.$vimgnew121.'" width="170"></a>';
                
                $hmtlContentBookDay.='<a href="http://promo.authorsxp.com/" target="_blank" ><img  class=" img-responsive" src="'.$vimgnew121.'" width="170"></a>';
            } else {
                
                $htmlContent .='<a href="http://promo.authorsxp.com/"><img class="img-responsive" src="http://promo.authorsxp.com/no_book_cover.jpg"></a>';
                
                $hmtlContentBookDay.= '<a href="http://promo.authorsxp.com/"><img class="img-responsive" src="http://promo.authorsxp.com/no_book_cover.jpg"></a>';
            }

            
            $htmlContent .='</div>
							</div>
							<div class="col-sm-9" style="width: 65%;float: left;">
							<h5 class="book-title" style="margin-top:0px;margin-bottom:11px;">
							<span style="text-decoration: none;font-size: 22px;margin-top: 0;color:#323232 !important;font-weight: 700;pointer-events: none;cursor: text;">'.substr($daily_deal["book_title"], 0, 80).'</span>
							<br><small class="book-author" style="color: #999;font-size: 13px;line-height: 1.5;">
							'.$daily_deal["author_name"].'</small>
							</h5>';
            
            $hmtlContentBookDay.='</div>
							</div>
							<div class="col-sm-9" style="width: 65%;float: left;margin: auto; margin-left: 10px;">
							<h5 class="book-title" style="margin-top:0px;margin-bottom:11px;">
							<span style="text-decoration: none;font-size: 22px;margin-top: 0;color:#323232 !important;font-weight: 700;pointer-events: none;cursor: text;">'.substr($daily_deal["book_title"], 0, 80).'</span>
							<br><small class="book-author" style="color: #999;font-size: 13px;line-height: 1.5;">
							'.$daily_deal["author_name"].'</small>
							</h5>';
            if($daily_deal['category'] == 'Elite Missions'){
                
                $htmlContent .='<h2 align="center"><font color="red">AXP READ &amp; REVIEW BOOK</font></h2>
                <font color="red"><center>Only click if willing to read &amp; review! It\'ll  be added to your Reader Dashboard.</center></font>';
                
                $hmtlContentBookDay.='<h2 align="center"><font color="red">AXP READ &amp; REVIEW BOOK</font></h2>
                <font color="red"><center>Only click if willing to read &amp; review! It\'ll  be added to your Reader Dashboard.</center></font>';
            }
            
            $htmlContent .='<p class="blurb" style="margin-top: 0px; margin-bottom: 2px;font-size:14px;line-height:1.4;color:#000;">'.substr($daily_deal["book_description"], 0, 355).'</p
							<p class="prices" style="margin-top:0px;margin-bottom:2px">
							<span class="vprice" style="color: red;font-size: 28px;font-weight: 400;text-decoration-color: -moz-use-text-color;text-decoration-style: solid;padding-bottom:10px;">'.$promo_price.'</span><span class="vprice" style="color: '.$fcolor.';font-size: 14px;font-weight: '.$fontw.';text-decoration-line: '.$tdec.';text-decoration-color: -moz-use-text-color;text-decoration-style: solid;margin-left:10px;padding-bottom:10px;">';
            
            $hmtlContentBookDay.='<p class="blurb" style="margin-top: 0px; margin-bottom: 2px;font-size:14px;line-height:1.4;color:#000;">'.substr($daily_deal["book_description"], 0, 355).'</p
							<p class="prices" style="margin-top:0px;margin-bottom:2px">
							<span class="vprice" style="color: red;font-size: 28px;font-weight: 400;text-decoration-color: -moz-use-text-color;text-decoration-style: solid;padding-bottom:10px;">'.$promo_price.'</span><span class="vprice" style="color: '.$fcolor.';font-size: 14px;font-weight: '.$fontw.';text-decoration-line: '.$tdec.';text-decoration-color: -moz-use-text-color;text-decoration-style: solid;margin-left:10px;padding-bottom:10px;">';
            if (in_array($usual_price, ['Free', 'New Release'])) {
                
                $htmlContent .= $usual_price;
                
                $hmtlContentBookDay.= $usual_price;
            } else {
                
                $htmlContent .='$'.$usual_price;
                
                $hmtlContentBookDay.='$'.$usual_price;
            }
           
            $htmlContent .='</span>
			</p>';

            $htmlContent .='<div class="retailer-links retailers" style="margin-top:5px;display:inline-block;">';
            $htmlContent .= '<table border="0" cellspacing="5" cellpadding="0"><tr>';
            
            $hmtlContentBookDay.='</span>
			</p>
            <div class="retailer-links retailers" style="margin-top:5px;display:inline-block;">
            <table border="0" cellspacing="5" cellpadding="0"><tr>';

            if ($functions->urlValidate($daily_deal['apple_link'])) {
                
                $htmlContent .= '<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:105px;color:#fff">';
                $htmlContent .='<a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=apple">Apple iBooks</a>';
                $htmlContent .= '</td>';
                
                $hmtlContentBookDay.='<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:105px;color:#fff">
                                    <a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=apple">Apple iBooks</a>
                                    </td>';
            }
            if ($functions->urlValidate($daily_deal['universal_link'])) {
                
                $htmlContent .= '<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:90px;color:#fff">';
                $htmlContent .='<a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=universal">Universal Link</a>';
                $htmlContent .= '</td>';
                
                $hmtlContentBookDay.='<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:90px;color:#fff">
                                    <a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=universal">Universal Link</a>
                                    </td>';
            }
            if ($functions->urlValidate($daily_deal['amazon_link'])) {
                
                $htmlContent .= '<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:90px;color:#fff">';
                $htmlContent .='<a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="https://promo.authorsxp.com/book.php?vid='.$vbookid.'&link_type=amz">Amazon US</a>';
                $htmlContent .= '</td>';
                
                $hmtlContentBookDay.='<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:90px;color:#fff">
                                    <a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="https://promo.authorsxp.com/book.php?vid='.$vbookid.'&link_type=amz">Amazon US</a>
                                    </td>';
            }
            if ($functions->urlValidate($daily_deal['noblel_link'])) {
                
                $htmlContent .= '<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:130px;color:#fff">';
                $htmlContent .='<a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=noble">Barnes & Noble</a>';
                $htmlContent .= '</td>';
                
                $hmtlContentBookDay.='<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:130px;color:#fff">
                                    <a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=noble">Barnes & Noble</a>
                                    </td>';
            }
            if ($functions->urlValidate($daily_deal['google_play_link'])) {
                
                $htmlContent .= '<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:70px;color:#fff">';
                $htmlContent .='<a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=gplay">Google</a>';
                $htmlContent .= '</td>';
                
                $hmtlContentBookDay.='<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:70px;color:#fff">
                                    <a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=gplay">Google</a>
                                    </td>';
            }
            if ($functions->urlValidate($daily_deal['kobo_link'])) {
                
                $htmlContent .= '<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:50px;color:#fff">';
                $htmlContent .='<a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=kobo">Kobo</a>';
                $htmlContent .= '</td>';
                
                $hmtlContentBookDay.='<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:50px;color:#fff">
                                <a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=kobo">Kobo</a>
                                </td>';
            }
            if ($functions->urlValidate($daily_deal['amazonuk_link'])) {
                
                $htmlContent .= '<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:95px;color:#fff">';
                $htmlContent .='<a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=amzuk">Amazon UK</a>';
                $htmlContent .= '</td>';
                
                $hmtlContentBookDay.='<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:95px;color:#fff">
                                <a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=amzuk">Amazon UK</a>
                                </td>';
            }
            if ($functions->urlValidate($daily_deal['amazonin_link'])) {
                
                $htmlContent .= '<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:90px;color:#fff">';
                $htmlContent .='<a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=amzin">Amazon IN</a>';
                $htmlContent .= '</td>';
                
                $hmtlContentBookDay.='<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:90px;color:#fff">
                                    <a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=amzin">Amazon IN</a>
                                    </td>';
            }
            if ($functions->urlValidate($daily_deal['amazonca_link'])) {
                
                $htmlContent .= '<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:90px;color:#fff">';
                $htmlContent .='<a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=amzca">Amazon CA</a>';
                $htmlContent .= '</td>';
                
                $hmtlContentBookDay.='<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:90px;color:#fff">
                                <a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=amzca">Amazon CA</a>
                                </td>';
            }
            if ($functions->urlValidate($daily_deal['amazonau_link'])) {
                
                $htmlContent .= '<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:90px;color:#fff">';
                $htmlContent .='<a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=amzau">Amazon AU</a>';
                $htmlContent .= '</td>';
                
                $hmtlContentBookDay.='<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:90px;color:#fff">
                                <a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=amzau">Amazon AU</a>
                                </td>';                
            }

            if ($functions->urlValidate($daily_deal['bookfunnel_link'])) {
                
                $htmlContent .= '<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:90px;color:#fff">';
                $htmlContent .='<a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=bookfunnel">BookFunnel</a>';
                $htmlContent .= '</td>';
                
                $hmtlContentBookDay.='<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:90px;color:#fff">
                                    <a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=bookfunnel">BookFunnel</a>
                                    </td>';
            }
            if ($functions->urlValidate($daily_deal['instafreebie_link'])) {
                
                $htmlContent .= '<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:90px;color:#fff">';
                $htmlContent .='<a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=instafreebie">Instafreebie</a>';
                $htmlContent .= '</td>';
                
                $hmtlContentBookDay.='<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:90px;color:#fff">
                                <a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=instafreebie">Instafreebie</a>
                                </td>';
            }
            if ($functions->urlValidate($daily_deal['website_link'])) {
            
                $htmlContent .= '<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:90px;color:#fff">';
                $htmlContent .='<a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=website">Website</a>';
                $htmlContent .= '</td>';
                
                $hmtlContentBookDay.='<td align="center" style="text-align:center;display:inline-block;text-decoration:none;background-color:#3490bf;border-radius:5px;font-weight:700;margin-bottom:8px;font-size:15px;margin-right:10px;padding:6px 15px;width:90px;color:#fff">
                            <a class="btn btn-primary btn-xs " style="color:white;text-decoration:none;text-align:center;" href="http://links.authorsxp.com/index.php?book_id='.$vbookid.'&link_type=website">Website</a>
                            </td>';
            }
            
            $htmlContent .='</tr></table>';
            $htmlContent .='</div>';

            $htmlContent .='
				<p class="categories small" style="margin-top:2px;">
				<strong></strong>
				<span style="text-decoration: none;color: #636363 !important;font-style:italic">'.implode(", ", $vdailydealscat).'</span>
				</p>';
            
            $hmtlContentBookDay.='</tr></table>
                                </div>
                                <p class="categories small" style="margin-top:2px;margin-bottom: 2px;">
                                <strong></strong>
                                <span style="text-decoration: none;color: #636363 !important;font-style:italic">'.implode(", ", $vdailydealscat).'</span>
                                </p>';

            if ($daily_deal['Kindle_Unlimited'] !='') {
                
                $htmlContent .='<p style="color:red;margin-bottom: 0px;">FREE on Kindle Unlimited</p>';
                
                $hmtlContentBookDay.='<p style="color:red;margin-bottom: 0px;">FREE on Kindle Unlimited</p>';
            }
            if ($daily_deal['osm_Series_Link'] !='') {
                
                $htmlContent .='<a href="'. $daily_deal['osm_Series_Link'] .'">See this whole series!</a>';
                
                $hmtlContentBookDay.='<a href="'. $daily_deal['osm_Series_Link'] .'">See this whole series!</a>';
            }
            
            $htmlContent .='</div>
				</div>

			</div><hr style="border-bottom:0px solid #ccc;width:100%;margin:0 auto;margin-bottom:15px;margin-top:15px">';
            
         
           $hmtlContentBookDay.='</div>
				</div>
			    </div>';

                
            $show_in_list = 'show';
            if(array_key_exists($daily_deal['subscriber_id'], $this->array_field_values)){
                $option = $this->array_field_values[$daily_deal['subscriber_id']];
                if(!array_key_exists(218, $option)){
                    $show_in_list = 'hidden';
                }
            };
            
            if($show_in_list == 'show'){
                $htmlContentAcum.=$htmlContent;
            } 

           
           
              if(count($this->array_field_values) == 0) continue;
              if(!array_key_exists($daily_deal['subscriber_id'], $this->array_field_values)) continue;
              //217 book of the day option - graphic ad option
              //218 graphic ad image         
            $acum++;
              

              $is_with_graphic = false;            
              $option = $this->array_field_values[$daily_deal['subscriber_id']];
              $graphic = '';
              $link = '';
              $img_link = "http://promo.authorsxp.com/no_book_cover.jpg";

            
            if (array_key_exists(218, $option)) {
                $is_with_graphic = true;
                $graphic = $this->array_field_values[$daily_deal['subscriber_id']]['218'];
                $regex = '/\b((https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/i';
                $is_url_graphic = preg_match($regex, $graphic);

                if ($is_url_graphic) {
                    $img_link = $graphic;
                }else{
                    $img_link = "https://authorsxp.com/media/com_osmembership/upload/$graphic";
                }
                if ($functions->urlValidate($daily_deal['universal_link'])) {
                    $link = $daily_deal['universal_link'];
                  }else if($functions->urlValidate($daily_deal['amazon_link'])){
                    $link = $daily_deal['amazon_link'];
                  }
            }        
            if($is_with_graphic){

                if($graphic !== ''){                   
                    // $addContentBookDay .='<div style=" width: 300px; min-width: 38%; display: inline-block;justify-content: center; margin: auto;vertical-align: middle;">
                    // <a href="'.$link.'" target="_blank">
                    // <img style="width: 300px;height:300px;object-fit: fill;margin: 0 auto;" src="'.$img_link.'">
                    // </a>
                    // </div>';
                    $contentBookDayGraphic ='<div style=" width: 300px; min-width: 38%; display: table-cell;justify-content: center; margin: auto;vertical-align: middle;">
                    <a href="'.$link.'" target="_blank">
                    <img style="width: 300px;height:300px;object-fit: fill;margin: 0 auto;" src="'.$img_link.'">
                    </a>
                    </div>';
                    if($acum%2==0){
                        $addContentBookDay .=$contentBookDayGraphic."</div>";
                        $addContentBookDay .= "<div>";
                    }else{
                        $addContentBookDay .=$contentBookDayGraphic;
                    }
                }else{
                    // $addContentBookDay .='<div style=" width: 300px; min-width: 38%;display: inline-block;justify-content: center; margin: auto;vertical-align: middle;">
                    // <img style="width: 300px;height:300px;object-fit: fill;margin: 0 auto;" src="http://promo.authorsxp.com/no_book_cover.jpg">
                    // </div>';
                    $contentBookDayGraphic ='<div style=" width: 300px; min-width: 38%;display: table-cell;justify-content: center; margin: auto;vertical-align: middle;">
                    <img style="width: 300px;height:300px;object-fit: fill;margin: 0 auto;" src="http://promo.authorsxp.com/no_book_cover.jpg">
                    </div>';
                    if($acum%2==0){
                        $addContentBookDay .=$contentBookDayGraphic."</div>";
                        $addContentBookDay .= "<div>";
                    }else{
                        $addContentBookDay .=$contentBookDayGraphic;
                    }
                }
                

            }else{
                // $addContentBookDay .= $hmtlContentBookDay;
                if($acum%2==0){
                    $addContentBookDay .=$hmtlContentBookDay."</div>";
                    $addContentBookDay .= "<div>";
                }else{
                    $addContentBookDay .=$hmtlContentBookDay;
                }
           
            } 

      
             
        }  

        // $htmlContent .= '</div>
		// </body>
		// </html>';
        $htmlContentAcum.= '</div></div>
		</body>
		</html>';
        // $htmlContentAcum.= '</div>
		// </body>
		// </html>';
    
      $addContentBookDay.='</div>';

        $date1=date("l");
        $subject = "Your Daily Books for ". $date1;
        $to = $email;
        
        $htmlContent2='<div style="width:100%;margin:0 auto;background-color:#3490bf;padding:10px 0px 14px 0;margin-bottom:20px;"><span style="font-family: sans-serif;padding-top:0px;padding-left: 150px; font-size: 23px; color: rgb(255, 255, 255); width: 200px; float: left; font-family: sans-serif;"><a href="http://axpbooks.com" style="text-decoration:none;color:#fff">AXP Books</a></span><span style="width:100%;text-align:right;margin-left:25%;font-size:18px;color:#fff;font-family: sans-serif;"><a href="http://promo.authorsxp.com" style="text-decoration:none;color:#fff;font-size:13px" target="_blank">View Online</a> | <a href="https://authorsxp.com/refer" style="text-decoration:none;color:#fff;font-size:13px" target="_blank">Refer a Friend</a> | <a href="http://promo.authorsxp.com/chosecategory.php?email='. $to .'" style="text-decoration:none;color:#fff;font-size:13px" target="_blank">Edit Preferences</a></span></div><div style="width:100%;margin:0 auto;align:center;padding:5px;"><center></center></div><BR><div align="center"></div>
            <div style="width:90%; justify-content:center; align-items:center;margin: auto;">
            <div style="width:100%; margin: 0 auto;"><div>'. $addContentBookDay .'</div></div>
            </div>
            <div style="width:100%;margin:0 auto;background-color:#ffffff;padding:10px 0px 5px 0;margin-bottom:10px;">
        
            <P><div align="center"><strong>TIP:</strong> Change your book categories or unsubscribe by clicking EDIT PREFERENCES at the top of the dashboard.</div></div>';

        $htmlcontentimg='<p align="center"><a href="https://authorsxp.com/giveaway"><img src="https://authorsxp.com/images/winbooks.jpg"><br></a><a href="https://authorsxp.com/fresh-friday-new-release-books"><img src="https://authorsxp.com/images/newreleasebutton.jpg"></a><a href="https://authorsxp.com/win-following-authors"><img src="https://authorsxp.com/images/followbutton.jpg"></a></p>';

        $htmlContent11113='<div class="col-md-12 " style="width: 100%;text-align:center;background-color:#eeeeee;padding:10px 0;margin:20px 0;font-weight:normal;font-size:12px;position: relative;color:#000 !important"><p style="color:#000 !important">Prices may change without notice or be dependent upon region or retailer  - please verify that the deal is still available before downloading.</p><p style="color:#000 !important">AuthorsXP - USA</p></div>';

        $htmlContent1='
            <div class="col-md-12" style="width: 100%; padding-left: 15px; padding-right: 15px; position: relative;">
                <div style="text-decoration: none; font-weight: 700; margin-bottom: 8px; font-size:15px;
                    margin-right: 4px; padding-bottom: 5px; padding-left: 5px; padding-right: 5px;
                    padding-top:0px;margin-left:22%; position:absolute;bottom:0px">
                    <a class="btn btn-primary btn-xs"
                        href="http://promo.authorsxp.com/chosecategory.php?email='. $to .'">
                        Change your preferred categories</a> |
                    <a href="http://promo.authorsxp.com/unsubscribe.php?email='. $to .'"
                        target="_blank">Discontinue</a> |
                    <a href="https://authorsxp.com/for-authors/daily-deal"
                        target="_blank">Authors: Add Your Books</a>
                </div>
            </div>
        ';
        $htmlContent12=$htmlContent2.$htmlContentAcum.$htmlcontentimg.$htmlContent11113.$htmlContent1;

        $from = 'amy@authorsxp.com';
        $name = 'AuthorsXP';
        $subj = $subject;
        $msg = $htmlContent12; 

        if ($has_books) {
            Mail::to($to)->send(new SendDailyDealEmail(
                $to, 
                $from,  
                $name,  
                $subj,
                $msg 
            ));

            echo "EMAIL SENT"."\n";
           
        }
    }
}
