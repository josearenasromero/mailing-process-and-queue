<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyDealEmail extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id',
        'subscriber_id',
        'book_title',
        'book_description',
        'category',
        'usual_price',
        'promotion_price',
        'cover_image',
        'amazon_link',
        'universal_link',
        'amazonuk_link',
        'amazonin_link',
        'amazonca_link',
        'amazonau_link',
        'featured_book',
        'google_play_link',
        'kobo_link',
        'author_name',
        'deal_date',
        'enddate',
        'feature',
        'apple_link',
        'noblel_link',
        'bookfunnel_link',
        'instafreebie_link',
        'website_link',
        'status',
        'do_or',
        'created_date',
        'dateflaxible',
        'osm_Series_Link',
        'Kindle_Unlimited',
        'chirp_link',
        'audible_link',
        'other_audio_link',
        'link_id'
    ];

    protected $table = 'smtda_osmembership_daily_deals';
    public $timestamps = false;
}
