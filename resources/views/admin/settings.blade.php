@extends('layouts.app')
@section('title','Platform Settings — Admin')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.admin-sidebar')
    <main>
      <h2 class="mb-1">Platform Settings</h2>
      <p class="text-muted mb-4">Configure commission, payouts, and platform behaviour</p>
      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      <form action="{{ route('admin.settings.update') }}" method="POST">@csrf
        @foreach([
          ['payment','Payment & Settlement',[['default_commission','Platform Commission (%)','15','Percentage deducted from each sale'],['min_payout_threshold','Min Payout Amount (₹)','500','Minimum balance required to request payout'],['settlement_hours','Settlement Hold (hours)','48','Hours to hold payment before releasing to seller wallet']]],
          ['general','General',[['platform_name','Platform Name','Naukaridarpan',''],['platform_email','Support Email','support@naukaridarpan.com',''],['platform_phone','Support Phone','+91-9876543210','']]],
          ['ai','AI Providers',[
            ['ai_provider','Active AI Provider','openai','openai or gemini'],
            ['openai_api_key','OpenAI API Key','','Stored in DB; keep private'],
            ['openai_model','OpenAI Model','gpt-4o-mini','Example: gpt-4o-mini'],
            ['gemini_api_key','Gemini API Key','','Stored in DB; keep private'],
            ['gemini_model','Gemini Model','gemini-1.5-flash','Example: gemini-1.5-flash'],
          ]],
          ['blog','AI Blog',[
            ['auto_blog_enabled','Auto Blog Enabled (1=yes, 0=no)','1',''],
            ['auto_blog_language','Blog Language','English','English or Hindi'],
            ['blog_topics_json','Blog Topics JSON','','JSON map of categories to topics; leave blank to use defaults'],
            ['weekly_current_affairs_enabled','Weekly Current Affairs (1=yes, 0=no)','1',''],
            ['weekly_historical_news_enabled','Weekly Historical News (1=yes, 0=no)','1',''],
            ['weekly_sports_news_enabled','Weekly Sports News (1=yes, 0=no)','1',''],
            ['weekly_top_news_enabled','Weekly Most Important News (1=yes, 0=no)','1',''],
            ['blog_ads_code','Blog Ads HTML','','Paste Google Ads/AdSense snippet'],
          ]],
          ['blog_images','Blog Images',[
            ['image_source_default','Image Source Default','google','google or pexels'],
            ['google_cse_api_key','Google CSE API Key','',''],
            ['google_cse_cx','Google CSE CX','',''],
            ['pexels_api_key','Pexels API Key','',''],
          ]],
          ['scraper','Scraper AI',[
            ['scraper_ai_enabled','Scraper AI Enabled (1=yes, 0=no)','0',''],
            ['scraper_ai_provider','Scraper AI Provider','openai','openai or gemini'],
            ['scraper_ai_model','Scraper AI Model','gpt-4o-mini','Example: gpt-4o-mini / gemini-1.5-flash'],
          ]],
          ['upload','Uploads',[['max_upload_size_mb','Max PDF Upload Size (MB)','50','']]],
        ] as [$group,$heading,$fields])
        <div class="card card-static mb-3">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-l);font-weight:600;font-family:var(--fu)">{{ $heading }}</div>
          <div class="card-body">
            @foreach($fields as [$key,$label,$default,$hint])
            <div class="form-group">
              <label class="form-label">{{ $label }}</label>
              <input type="text" name="{{ $key }}" class="form-control" value="{{ $settings[$key]->value ?? $default }}">
              @if($hint)<div class="form-hint">{{ $hint }}</div>@endif
            </div>
            @endforeach
          </div>
        </div>
        @endforeach
        <button type="submit" class="btn btn-primary btn-lg">Save All Settings</button>
      </form>
    </main>
  </div>
</div>
@endsection
