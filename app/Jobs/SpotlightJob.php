<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\MeetauthorEmail;
use App\Helpers\CategoryHelper;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class SpotlightJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;

    public function __construct()
    {
        $this->onConnection('sync');
    }
    
    public $tries = 3;  

    public function uniqueId(): string
    {
        return 'SpotlightJob in process';
    }
   
    public function handle(): void
    {      
        $start_time = microtime(true);
                
        $category_helper = new CategoryHelper();       
        
        $current_date = date('Y-m-d');
        $timepicker = date('H:i:s');                         
    
        $allsearchresult = MeetauthorEmail::where('date','=', $current_date)
        ->where('time', '<=', $timepicker)
        ->where('status','=', 0)
        ->where('type','=',0)
        ->get();
    
        $templates = array();
        
        foreach ($allsearchresult as $row_templates) {
            $template_categories = $category_helper->getCategoriesByTemplate($row_templates["id"]);           
            
            $row_templates["smtda_meetauthor_email_cat"] = $template_categories;

            $templates[] = $row_templates;
        }

        $total_templates = count($templates);
       
        $temp_ctemplates = $total_templates;

        if ($total_templates < 1) {           
            // Log::info('No templates found to send.');
            echo 'No templates found to send.';
            $this->queueData([
                'Template' => 'No templates found to send',
            ]);
            return;
        }       

        echo "Total Templates that apply right now: " . $temp_ctemplates ."\n";
        echo "----------------------"."\n";

        $queue_data_templates =[];
        foreach ($templates as $template) {
            echo "Template Name: " . $template["email_sub"] ."\n";
            echo "Template Date: " . $template["date"] ."\n";
            echo "Template Hour: " . $template["time"] ."\n";
            echo "----------------------"."\n";
            
            $data_template = [
                'Template' => $template['email_sub'],
                'Date' => $template['date'],
                'Time' => $template['time']
            ];
            $queue_data_templates[] = $data_template;        
                                
        }        
        echo "OUTPUT:"."\n";  
        $this->queueData(
            $queue_data_templates
        );     
        
            
        foreach ($templates as $template) {
            //update the status to send to avoid duplicates                    
            MeetauthorEmail::where('id', $template["id"])
            ->update(['status' => 2]);
        }
        
        $users_categories = $category_helper->getCategoriesByUser();     
        
        foreach ($users_categories as $email => $categories) {
           
            if (count($categories) < 1) continue;
                
           
            foreach ($templates as $template) {

                $cat_flag = 0;
            
                if (count($template["smtda_meetauthor_email_cat"]) < 1) {
                    continue;        
                }

                $do_or = $template["do_or"];
               
                foreach ($template["smtda_meetauthor_email_cat"] as $tcat) {    
                    
                    if (in_array($tcat["name"], $categories)) {   
                                        
                        $cat_flag++;
                    
                    }
                }                
            
                
                if ($do_or) {
                    if ($cat_flag < 1) continue;     
                } else {
                    
                    if ($cat_flag != count($template["smtda_meetauthor_email_cat"])) continue;              
                } 

                if (!Cache::has($email . $template["email_sub"])) {

                    TemplateJob::dispatch($email, 
                            $template["email_sub"],
                            $template["template"],
                            $template["date"],
                            $template["time"]);  
                }
                
            }
        }
         echo 'All templates added to queue.'."\n";
     
        $end_time = microtime(true);
        $total_Time = $end_time - $start_time;
        echo 'Current memory usage: ' . memory_get_usage() . ' bytes';
        echo "Total processing time: " . round($total_Time / 60, 2) . " minutes.\n";
    }
}
