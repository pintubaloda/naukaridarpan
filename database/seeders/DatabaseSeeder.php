<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\SellerProfile;
use App\Models\Category;
use App\Models\PlatformSetting;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── ADMIN ─────────────────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Naukaridarpan Admin',
            'email'    => 'admin@naukaridarpan.com',
            'password' => Hash::make('Admin@1234'),
            'role'     => 'admin',
            'is_active'=> true,
        ]);

        // ── DEMO SELLER ───────────────────────────────────────────────
        $seller = User::create([
            'name'     => 'Prof. Ramesh Kumar',
            'email'    => 'seller@naukaridarpan.com',
            'password' => Hash::make('Seller@1234'),
            'role'     => 'seller',
            'is_active'=> true,
        ]);
        SellerProfile::create([
            'user_id'       => $seller->id,
            'username'      => 'prof-ramesh-kumar',
            'bio'           => 'IAS officer turned educator. 15 years of UPSC coaching experience. Helped 500+ students crack Civil Services.',
            'qualification' => 'IAS (Retd.), M.A. Public Administration',
            'institution'   => 'Delhi UPSC Academy',
            'subjects'      => ['UPSC GS','Public Administration','Ethics'],
            'city'          => 'New Delhi',
            'state'         => 'Delhi',
            'rating'        => 4.8,
            'total_reviews' => 312,
            'total_sales'   => 1450,
            'is_verified'   => true,
        ]);

        // ── DEMO STUDENT ──────────────────────────────────────────────
        User::create([
            'name'     => 'Priya Sharma',
            'email'    => 'student@naukaridarpan.com',
            'password' => Hash::make('Student@1234'),
            'role'     => 'student',
            'is_active'=> true,
        ]);

        // ── CATEGORIES ───────────────────────────────────────────────
        $cats = [
            ['name'=>'UPSC',         'slug'=>'upsc',       'icon'=>'🏛️', 'sort'=>1],
            ['name'=>'SSC',          'slug'=>'ssc',        'icon'=>'📋', 'sort'=>2],
            ['name'=>'Banking',      'slug'=>'banking',    'icon'=>'🏦', 'sort'=>3],
            ['name'=>'Railway',      'slug'=>'railway',    'icon'=>'🚂', 'sort'=>4],
            ['name'=>'State PSC',    'slug'=>'state-psc',  'icon'=>'🗺️', 'sort'=>5],
            ['name'=>'Defence',      'slug'=>'defence',    'icon'=>'🎖️', 'sort'=>6],
            ['name'=>'Police',       'slug'=>'police',     'icon'=>'👮', 'sort'=>7],
            ['name'=>'Teaching',     'slug'=>'teaching',   'icon'=>'📚', 'sort'=>8],
            ['name'=>'NEET',         'slug'=>'neet',       'icon'=>'🩺', 'sort'=>9],
            ['name'=>'JEE',          'slug'=>'jee',        'icon'=>'⚙️', 'sort'=>10],
            ['name'=>'GATE',         'slug'=>'gate',       'icon'=>'🔬', 'sort'=>11],
            ['name'=>'Law (CLAT)',   'slug'=>'law',        'icon'=>'⚖️', 'sort'=>12],
        ];
        foreach ($cats as $c) {
            Category::create(['name'=>$c['name'],'slug'=>$c['slug'],'icon'=>$c['icon'],'sort_order'=>$c['sort'],'is_active'=>true]);
        }

        // ── PLATFORM SETTINGS ─────────────────────────────────────────
        $settings = [
            ['key'=>'default_commission',      'value'=>'15',      'group'=>'payment'],
            ['key'=>'min_payout_threshold',    'value'=>'500',     'group'=>'payment'],
            ['key'=>'settlement_hours',        'value'=>'48',      'group'=>'payment'],
            ['key'=>'platform_name',           'value'=>'Naukaridarpan', 'group'=>'general'],
            ['key'=>'platform_email',          'value'=>'support@naukaridarpan.com', 'group'=>'general'],
            ['key'=>'platform_phone',          'value'=>'+91-9876543210', 'group'=>'general'],
            ['key'=>'ai_provider',             'value'=>'openai',  'group'=>'ai'],
            ['key'=>'openai_api_key',          'value'=>'',        'group'=>'ai'],
            ['key'=>'openai_model',            'value'=>'gpt-4o-mini', 'group'=>'ai'],
            ['key'=>'gemini_api_key',          'value'=>'',        'group'=>'ai'],
            ['key'=>'gemini_model',            'value'=>'gemini-1.5-flash', 'group'=>'ai'],
            ['key'=>'auto_blog_enabled',       'value'=>'1',       'group'=>'blog'],
            ['key'=>'auto_blog_language',      'value'=>'English', 'group'=>'blog'],
            ['key'=>'blog_topics_json',        'value'=>'',        'group'=>'blog'],
            ['key'=>'weekly_current_affairs_enabled', 'value'=>'1', 'group'=>'blog'],
            ['key'=>'weekly_historical_news_enabled', 'value'=>'1', 'group'=>'blog'],
            ['key'=>'weekly_sports_news_enabled', 'value'=>'1', 'group'=>'blog'],
            ['key'=>'weekly_top_news_enabled',  'value'=>'1',       'group'=>'blog'],
            ['key'=>'blog_ads_code',           'value'=>'',        'group'=>'blog'],
            ['key'=>'image_source_default',   'value'=>'google',  'group'=>'blog_images'],
            ['key'=>'google_cse_api_key',      'value'=>'',        'group'=>'blog_images'],
            ['key'=>'google_cse_cx',           'value'=>'',        'group'=>'blog_images'],
            ['key'=>'pexels_api_key',          'value'=>'',        'group'=>'blog_images'],
            ['key'=>'scraper_ai_enabled',      'value'=>'0',       'group'=>'scraper'],
            ['key'=>'scraper_ai_provider',     'value'=>'openai',  'group'=>'scraper'],
            ['key'=>'scraper_ai_model',        'value'=>'gpt-4o-mini', 'group'=>'scraper'],
            ['key'=>'scraper_enabled',         'value'=>'1',       'group'=>'scraper'],
            ['key'=>'max_upload_size_mb',      'value'=>'50',      'group'=>'upload'],
            ['key'=>'tao_enabled',             'value'=>'1',       'group'=>'exam'],
        ];
        foreach ($settings as $s) {
            PlatformSetting::create($s);
        }

        $this->command->info('✅ Naukaridarpan seeded! Admin: admin@naukaridarpan.com / Admin@1234');
    }
}
