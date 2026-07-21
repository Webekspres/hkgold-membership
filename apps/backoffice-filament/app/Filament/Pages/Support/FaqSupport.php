<?php

declare(strict_types=1);

namespace App\Filament\Pages\Support;

use App\Models\FaqItem;
use Illuminate\Support\Facades\DB;

class FaqSupport
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public static function syncItems(array $items): void
    {
        DB::transaction(function () use ($items): void {
            $existingIds = FaqItem::query()->pluck('id')->all();
            $submittedIds = [];

            foreach (array_values($items) as $sortOrder => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $question = isset($item['question']) && filled($item['question']) ? (string) $item['question'] : '';
                $answer = isset($item['answer']) && filled($item['answer']) ? (string) $item['answer'] : '';

                $faqId = $item['id'] ?? null;

                if (filled($faqId)) {
                    $faq = FaqItem::query()->find($faqId);

                    if ($faq === null) {
                        continue;
                    }

                    $faq->update([
                        'question' => $question,
                        'answer' => $answer,
                        'sort_order' => $sortOrder,
                    ]);

                    $submittedIds[] = $faq->id;

                    continue;
                }

                $faq = FaqItem::query()->create([
                    'question' => $question,
                    'answer' => $answer,
                    'sort_order' => $sortOrder,
                ]);

                $submittedIds[] = $faq->id;
            }

            $orphanedIds = array_diff($existingIds, $submittedIds);

            if (! empty($orphanedIds)) {
                FaqItem::query()->whereIn('id', $orphanedIds)->delete();
            }
        });
    }
}
