<?php

declare(strict_types=1);

namespace App\Filament\Pages\Support;

use App\Models\FaqItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FaqSupport
{
    private const QUESTION_MAX_LENGTH = 255;

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public static function syncItems(array $items): void
    {
        DB::transaction(function () use ($items): void {
            $existingIds = FaqItem::query()->pluck('id')->all();
            $submittedIds = [];
            $sortOrder = 0;

            foreach (array_values($items) as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $question = self::normalizeQuestion($item['question'] ?? null);
                $answer = self::normalizeAnswer($item['answer'] ?? null);

                // Form already requires both fields; skip blank rows if called outside Filament.
                if ($question === null || $answer === null) {
                    continue;
                }

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
                    $sortOrder++;

                    continue;
                }

                $faq = FaqItem::query()->create([
                    'question' => $question,
                    'answer' => $answer,
                    'sort_order' => $sortOrder,
                ]);

                $submittedIds[] = $faq->id;
                $sortOrder++;
            }

            $orphanedIds = array_diff($existingIds, $submittedIds);

            if (! empty($orphanedIds)) {
                FaqItem::query()->whereIn('id', $orphanedIds)->delete();
            }
        });
    }

    private static function normalizeQuestion(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $question = trim((string) $value);

        if ($question === '') {
            return null;
        }

        if (mb_strlen($question) > self::QUESTION_MAX_LENGTH) {
            throw new InvalidArgumentException(
                'Pertanyaan FAQ maksimal '.self::QUESTION_MAX_LENGTH.' karakter.',
            );
        }

        return $question;
    }

    private static function normalizeAnswer(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $answer = trim((string) $value);

        return $answer === '' ? null : $answer;
    }
}
