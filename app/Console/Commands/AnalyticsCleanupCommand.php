<?php

namespace App\Console\Commands;

use App\Models\AnalyticsDailySummary;
use App\Models\AnalyticsEvent;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:cleanup {--days=180 : Keep raw events newer than this many days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate and clean old analytics events';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = CarbonImmutable::now()->subDays($days);

        /** @var Collection<int, object{summary_date: string, event_type: string, page_url: ?string, device_type: ?string, country: ?string, events_count: int|string}> $summaries */
        $summaries = AnalyticsEvent::query()
            ->where('created_at', '<', $cutoff)
            ->selectRaw('date(created_at) as summary_date, event_type, page_url, device_type, upper(country) as country, count(*) as events_count')
            ->groupBy(DB::raw('date(created_at)'), 'event_type', 'page_url', 'device_type', DB::raw('upper(country)'))
            ->get();

        if ($summaries->isNotEmpty()) {
            $now = now();

            AnalyticsDailySummary::query()->upsert(
                $summaries->map(function (object $summary) use ($now): array {
                    return [
                        'summary_date' => $summary->summary_date,
                        'event_type' => $summary->event_type,
                        'page_url' => $summary->page_url,
                        'device_type' => $summary->device_type,
                        'country' => $summary->country,
                        'events_count' => (int) $summary->events_count,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->all(),
                ['summary_date', 'event_type', 'page_url', 'device_type', 'country'],
                ['events_count', 'updated_at'],
            );
        }

        $deletedRows = AnalyticsEvent::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info(sprintf(
            'Aggregated %d summary rows and deleted %d raw events older than %d days.',
            $summaries->count(),
            $deletedRows,
            $days,
        ));

        return self::SUCCESS;
    }
}
