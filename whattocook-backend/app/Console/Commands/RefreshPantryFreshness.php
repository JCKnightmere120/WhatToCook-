<?php

namespace App\Console\Commands;

use App\Models\PantryItem;
use Illuminate\Console\Command;

class RefreshPantryFreshness extends Command
{
    protected $signature = 'pantry:refresh-freshness';

    protected $description = 'Mark pantry items due for a freshness review';

    public function handle(): int
    {
        $updated = PantryItem::query()->where('freshness_status', 'fresh')
            ->whereNotNull('freshness_review_date')->whereDate('freshness_review_date', '<=', today())
            ->update(['freshness_status' => 'review']);

        $this->info("Marked {$updated} pantry item(s) for review.");

        return self::SUCCESS;
    }
}
