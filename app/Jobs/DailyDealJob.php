<?php

namespace App\Jobs;

use App\Helpers\CategoryHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class DailyDealJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;
  
    public function __construct()
    {
        $this->onConnection('sync');
    }

    public $tries = 3;  

    public function uniqueId(): string
    {
        return 'DailyDealJob in process';
    }
  
    public function handle(): void
    {  
        $start_time = microtime(true);
        $category_helper = new CategoryHelper(); 
        $current_date = date('Y-m-d');        
        
        [$dd_array, $dd_categories]= $category_helper->getCategoriesByDD($current_date);
        
        $queue_data_templates =[];
        foreach ($dd_array as $template) {
            echo "Book tile: " . $template["book_title"] ."\n";
            echo "Date: " . $template["deal_date"] ."\n";
            echo "----------------------"."\n";
            
            $data_template = [
                'Book' => $template['book_title'],
                'Date' => $template['deal_date']
            ];
            $queue_data_templates[] = $data_template;        
                                
        }        
        echo "OUTPUT:"."\n";  
        $this->queueData(
            $queue_data_templates
        );   
        
        $users_categories = $category_helper->getCategoriesByUser();          
        $array_field_values = $category_helper->getBookDayByDD($current_date);        
        
        foreach ($users_categories as $email => $categories) {        

            $email = FILTER_VAR($email, FILTER_SANITIZE_EMAIL);
            if (count($categories) < 1) continue;
            $has_books = false;

            $queue_dd_data_templates =[];
            foreach ($dd_array as $daily_deal) {               

                $vbookid1 = $daily_deal['odd_id'];
    
                $vdailydealscat = $dd_categories[$vbookid1];
    
                if (count($vdailydealscat) < 1) continue;
    
                $do_or = $daily_deal['do_or'];
    
                $cat_flag = 0;
                foreach ($vdailydealscat as $cat_name) {
                    if (in_array($cat_name, $categories)) $cat_flag++;
                }
    
                if ($do_or) {
                    if ($cat_flag < 1) continue;
                } else {
                    if ($cat_flag != count($vdailydealscat)) continue;                    
                }
                $has_books = true;

                $dd_data_template = [
                    'Book' => $daily_deal['book_title'],
                    'Date' => $daily_deal['deal_date'],
                    'Destination' => $email
                ];
                $queue_dd_data_templates[] = $dd_data_template;
            } 
           
            if(!Cache::has('DD_' . $email . '_' . $current_date) && $has_books){                
                DDTemplateJob::dispatch($email, $dd_array, $dd_categories, $categories, $queue_dd_data_templates,$current_date,$array_field_values);
            }
        
        }

        $end_time = microtime(true);
        $total_Time = $end_time - $start_time;
        echo 'Current memory usage: ' . memory_get_usage() . ' bytes';
        echo "Total processing time: " . round($total_Time / 60, 2) . " minutes.\n";

    }
}
