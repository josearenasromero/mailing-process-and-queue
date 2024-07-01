<?php

namespace App\Helpers;

use App\Models\DailyDealEmail;
use App\Models\MembershipFieldValue;
use App\Models\SubEmail;
use App\Models\TemplateCategorie;
use Illuminate\Support\Facades\DB;

class CategoryHelper {

    public function getCategoriesByUser() {      
              
        $users_categories = [];
        $acum = 0;        
       
        $query_email = SubEmail::select('user_categories.user_id', 'smtda_sub_emails.email', DB::raw('GROUP_CONCAT(categories.name) as categories'))
        ->from('smtda_sub_emails')
        ->join('user_categories', 'smtda_sub_emails.id', '=', 'user_categories.user_id')
        ->join('categories', 'user_categories.category_id', '=', 'categories.id')
        ->where('smtda_sub_emails.user_status', '=', '0')
        ->groupBy('user_categories.user_id', 'smtda_sub_emails.email')        
        ->cursor();        
       
        foreach ($query_email as $row) { 
            
            $email = $row->email;
            
            if (!isset($users_categories[$email])) {
                $users_categories[$email] = [];
            }

            $categories_array = explode(',', $row->categories);
            $users_categories[$email] = $categories_array;
            $acum++;
            if ($acum % 10000 == 0) {
                echo "processed $acum records so far.\n";
            }
            
        }            

        echo "Processed $acum records\n";
        
        return $users_categories;
    }  

    public function getCategoriesByTemplate($temp_id) {      
       
        $query = TemplateCategorie::from('template_categories AS tc')
        ->select('c.name')
        ->join('categories AS c', 'tc.category_id', '=', 'c.id')
        ->where('tc.template_id', '=', $temp_id)
        ->orderBy('c.name','ASC')
        ->get();

        return $query;
    } 
    
    public function getCategoriesByDD($current_date) {
        $dd_array = [];
        $dd_categories = [];
        $query_dd = DailyDealEmail::select('*', 'smtda_osmembership_daily_deals.id as odd_id')
        ->from('smtda_osmembership_daily_deals')
        ->join('dd_categories', 'smtda_osmembership_daily_deals.id', '=', 'dd_categories.dd_id')
        ->join('categories', 'dd_categories.category_id', '=', 'categories.id')
        ->where('smtda_osmembership_daily_deals.deal_date', '=', $current_date)
        ->where('smtda_osmembership_daily_deals.status', '=', '1')
        ->orderByDesc('smtda_osmembership_daily_deals.feature')
        ->cursor();         
        
        foreach ($query_dd as $row) { 
            $dd_id = $row->odd_id;
            $dd_array[$dd_id] = $row;

            if (!isset($dd_categories[$dd_id])) {
                $dd_categories[$dd_id] = [];
            }

            $dd_categories[$dd_id][] = $row->name;
        }
        
        return  [$dd_array, $dd_categories];
    }

    public function getBookDayByDD($current_date) {
        $dd_array_field_value = [];
        
        $query_dd = MembershipFieldValue::select('smtda_osmembership_field_value.*') 
        ->from('smtda_osmembership_field_value')      
        ->join('smtda_osmembership_daily_deals', 'smtda_osmembership_daily_deals.subscriber_id', '=', 'smtda_osmembership_field_value.subscriber_id') 
        ->where('smtda_osmembership_daily_deals.deal_date', '=', $current_date)
        ->where('smtda_osmembership_daily_deals.status', '=', '1')
        ->where(function($query) {
            $query->where('smtda_osmembership_field_value.field_id', '=', '217')
                  ->orWhere('smtda_osmembership_field_value.field_id', '=', '218');
        })
        ->orderBy('subscriber_id', 'asc')
        ->cursor();    
        
        foreach ($query_dd as $row) { 
           
            if (!isset($dd_array_field_value[$row->subscriber_id])) {
                $dd_array_field_value[$row->subscriber_id] = [];
            }
            
            $dd_array_field_value[$row->subscriber_id][$row->field_id] = $row->field_value;
        }
        
        return $dd_array_field_value;
    }

}