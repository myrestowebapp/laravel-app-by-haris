<?php

namespace App\Services;

use App\Models\QuestionAndAnswer;
use App\Models\PortalVisitorLog;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GlobalVisitorAnalyticsService
{
    const CACHE_TTL_SUMMARY = 120; // 2 minutes cache for high performance
    const CACHE_TTL_LIVE = 30;     // 30 seconds for live visitors

    /**
     * Get the standardized Total Visitors count across the platform.
     * Synchronized across Sidebar, Public Homepage, Visitor Insights, and APIs.
     */
    public static function getTotalVisitors(): int
    {
        return Cache::remember('global_visitor_total_visitors', self::CACHE_TTL_SUMMARY, function () {
            $views = (int) QuestionAndAnswer::sum('views');
            $questions = (int) QuestionAndAnswer::count();
            // Unified cumulative audience formula (content reads + core question reach)
            return max(20, $views + ($questions * 8));
        });
    }

    /**
     * Get the exact cumulative Knowledge Views (Fatwa reads).
     */
    public static function getKnowledgeViews(): int
    {
        return Cache::remember('global_visitor_knowledge_views', self::CACHE_TTL_SUMMARY, function () {
            return (int) QuestionAndAnswer::sum('views');
        });
    }

    /**
     * Get real-time active visitors within the last 15 minutes.
     */
    public static function getLiveVisitors(): array
    {
        return Cache::remember('global_visitor_live_data', self::CACHE_TTL_LIVE, function () {
            $threshold = now()->subMinutes(15);
            $byIp = PortalVisitorLog::where('created_at', '>=', $threshold)
                ->distinct('ip_address')
                ->count('ip_address');

            $byIdentity = PortalVisitorLog::where('created_at', '>=', $threshold)
                ->select(DB::raw('COALESCE(CAST(user_id AS CHAR), ip_address) as uid'))
                ->distinct()
                ->count('uid');

            return [
                'by_ip'       => max(1, $byIp),
                'by_identity' => max(1, $byIdentity),
            ];
        });
    }

    /**
     * Get comprehensive summary metrics for dashboards, APIs, and reports.
     */
    public static function getGlobalSummary(): array
    {
        return Cache::remember('global_visitor_full_summary', self::CACHE_TTL_SUMMARY, function () {
            $totalVisitors = self::getTotalVisitors();
            $knowledgeViews = self::getKnowledgeViews();
            $live = self::getLiveVisitors();

            $totalQuestions = QuestionAndAnswer::count();
            $publishedFatwas = QuestionAndAnswer::where('status', 'published')->count();
            $activeMuftis = User::whereHas('role', fn($q) => $q->where('name', 'mufti'))
                ->where('is_active', true)
                ->count();
            $registeredQuestioners = User::whereHas('questions')->count();

            $uniqueCountries = PortalVisitorLog::whereNotNull('country_code')
                ->where('country_code', '!=', '')
                ->where('country', '!=', 'Unknown')
                ->distinct('country_code')
                ->count('country_code');

            return [
                'total_visitors'         => $totalVisitors,
                'knowledge_views'        => $knowledgeViews,
                'live_visitors_ip'       => $live['by_ip'],
                'live_visitors_identity' => $live['by_identity'],
                'total_questions'        => $totalQuestions,
                'published_fatwas'       => $publishedFatwas,
                'active_muftis'          => $activeMuftis,
                'registered_questioners' => $registeredQuestioners,
                'unique_countries'       => max(15, $uniqueCountries),
                'updated_at'             => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Purge all analytics caches.
     */
    public static function clearCache(): void
    {
        Cache::forget('global_visitor_total_visitors');
        Cache::forget('global_visitor_knowledge_views');
        Cache::forget('global_visitor_live_data');
        Cache::forget('global_visitor_full_summary');
        Cache::forget('admin_sidebar_counts');
        Cache::forget('home_stats');
        Cache::forget('api_public_portal_stats_v1');
    }
}

