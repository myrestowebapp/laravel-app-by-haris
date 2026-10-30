<?php

namespace App\Http\Controllers;

use App\Models\QuestionAndAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;
use App\Models\User;
use App\Services\FeaturedFatwaService;
use App\Helpers\HijriHelper;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $stats = \Illuminate\Support\Facades\Cache::remember('home_stats', 900, function () {
            return [
                'total'     => QuestionAndAnswer::count(),
                'published' => QuestionAndAnswer::where('status', 'published')->count(),
                'muftis'    => User::whereHas('role', fn($q) => $q->where('name', 'mufti'))->count(),
                'visitors'  => \App\Services\GlobalVisitorAnalyticsService::getTotalVisitors(),
            ];
        });

        $latestFatwas = \Illuminate\Support\Facades\Cache::remember('latest_fatwas', 1800, function () {
            return QuestionAndAnswer::with(['madhab', 'category', 'assignedMufti'])
                                 ->where('status', 'published')
                                 ->where('is_public', true)
                                 ->latest('published_at')
                                 ->take(6)
                                 ->get();
        });

        // Detect visitor country from IP (set to default since portal_visitor_logs table is dropped)
        $visitorCountry = '';

        // Get current Hijri date
        $hijriParts = explode('-', HijriHelper::now('n-j')); // month-day
        $hijriMonth = (int) ($hijriParts[0] ?? 0);
        $hijriDay   = (int) ($hijriParts[1] ?? 0);

        // Resolve featured fatwas using smart rules (graceful fallback on DB error)
        try {
            $featuredData = (new FeaturedFatwaService())->resolve($visitorCountry, $hijriMonth, $hijriDay);
        } catch (\Exception $e) {
            // Table may not exist yet — hide the section
            $featuredData = ['label' => '', 'sub' => '', 'fatwas' => collect(), 'rule' => null];
        }

        return view('home', compact('stats', 'latestFatwas', 'featuredData'));
    }

    public function contact()
    {
        return view('contact');
    }

    public function privacy()
    {
        return view('privacy');
    }

    public function terms()
    {
        return view('terms');
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|max:255',
            'message'    => 'required|string|min:10',
        ]);

        try {
            Mail::to('darulifta.kauzariyya@gmail.com')->send(new ContactFormMail($validated));
            return back()->with('success', 'Your message has been sent successfully. We will contact you soon.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send message: ' . $e->getMessage())->withInput();
        }
    }

    public function team()
    {
        $muftis = User::whereHas('role', fn($q) => $q->where('name', 'mufti'))
            ->where('is_active', true)
            ->withCount([
                'assignedQuestions as assigned_questions_count',
                'assignedQuestions as published_fatwas_count' => fn($q) => $q->where('status', 'published')
            ])
            ->orderByRaw('CASE WHEN mufti_sort_order = 0 THEN 9999 ELSE mufti_sort_order END ASC')
            ->orderByDesc('published_fatwas_count')
            ->paginate(999);

        return view('team', compact('muftis'));
    }

    /**
     * Generate dynamic XML Sitemap for Google, Bing, and AI Crawlers supporting all 4 languages
     * (English, Arabic, Urdu, Malayalam)
     */
    public function sitemap()
    {
        $fatwas = QuestionAndAnswer::where('status', 'published')
            ->where('is_public', true)
            ->latest('published_at')
            ->get(['id', 'title', 'body', 'updated_at', 'published_at']);

        $categories = \App\Models\Category::all(['id', 'slug', 'updated_at']);
        $muftis = User::whereHas('role', fn($q) => $q->where('name', 'mufti'))->where('is_active', true)->get(['id', 'updated_at']);

        $languages = ['en', 'ar', 'ur', 'ml'];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        // Helper to output multilingual alternates
        $addUrl = function($loc, $priority = '0.8', $changefreq = 'weekly', $lastmod = null) use (&$xml, $languages) {
            $lastmodStr = ($lastmod ?? now())->toAtomString();
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($loc) . "</loc>\n";
            
            foreach ($languages as $lang) {
                $separator = (strpos($loc, '?') !== false) ? '&' : '?';
                $langUrl = $loc . $separator . 'lang=' . $lang;
                $xml .= '    <xhtml:link rel="alternate" hreflang="' . $lang . '" href="' . htmlspecialchars($langUrl) . '"/>' . "\n";
            }
            $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($loc) . '"/>' . "\n";
            $xml .= "    <lastmod>{$lastmodStr}</lastmod>\n";
            $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
            $xml .= "    <priority>{$priority}</priority>\n";
            $xml .= "  </url>\n";
        };

        // 1. Homepage
        $addUrl(route('home'), '1.0', 'daily', now());

        // 2. Fatwa Archive
        $addUrl(route('fatwa.archive'), '0.9', 'daily', now());

        // 3. Team & Scholar Directory
        $addUrl(route('team'), '0.7', 'weekly', now());

        // 4. Contact
        $addUrl(route('contact'), '0.5', 'monthly', now());

        // 4b. Legal: Privacy Policy & Terms of Service
        $addUrl(route('privacy'), '0.4', 'monthly', now());
        $addUrl(route('terms'), '0.4', 'monthly', now());

        // 5. Published Fatwas (Scrollable: Question, Answer, References, Mufti Name)
        foreach ($fatwas as $f) {
            $lastmod = $f->updated_at ?? $f->published_at ?? now();
            $addUrl(route('fatwa.show', $f), '0.8', 'weekly', $lastmod);
        }

        // 6. Categories Archive
        foreach ($categories as $cat) {
            $lastmod = $cat->updated_at ?? now();
            $addUrl(route('fatwa.archive', ['category' => $cat->slug ?: $cat->id]), '0.7', 'weekly', $lastmod);
        }

        // 7. Muftis Scholarly Portfolios
        foreach ($muftis as $m) {
            $lastmod = $m->updated_at ?? now();
            $addUrl(route('fatwa.mufti.archive', $m->id), '0.7', 'weekly', $lastmod);
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * AI-Crawler & Search Engine friendly robots.txt
     */
    public function robotsTxt()
    {
        $sitemapUrl = route('sitemap');

        $content = <<<TXT
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /mufti/
Disallow: /login
Disallow: /register
Disallow: /password/

# AI Crawlers & LLM Indexers Access
User-agent: GPTBot
Allow: /

User-agent: ChatGPT-User
Allow: /

User-agent: Google-Extended
Allow: /

User-agent: ClaudeBot
Allow: /

User-agent: PerplexityBot
Allow: /

User-agent: Applebot-Extended
Allow: /

Sitemap: {$sitemapUrl}
TXT;

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
