<?php

namespace App\Traits;

use App\Constants\OpenAIModel;
use App\Models\Translation;
use App\Models\Language;
use App\Services\ModelAI\OpenAIService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Log;

trait Translatable
{
    /**
     * @return void
     */
    protected static function bootTranslatable(): void
    {
        static::created(function ($model) {
            if ($model->shouldAutoTranslate()) {
                $model->generateTranslations();
            }
        });

        static::updated(function ($model) {
            if ($model->shouldAutoTranslate() && $model->wasTranslatedAttributeChanged()) {
                $model->generateTranslations();
            }
        });

        static::deleted(function ($model) {
            $model->translations()->delete();
        });
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'entity', 'entity_type', 'entity_id');
    }

    /**
     * Lấy bản dịch theo locale
     */
    public function getTranslation(string $locale)
    {
        return $this->translations()->where('locale', $locale)->first();
    }

    /**
     * Lấy giá trị đã dịch theo locale và attribute
     */
    public function getTranslatedAttribute(string $attribute, string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        $translation = $this->getTranslation($locale);

        if (!$translation) {
            return $this->getAttribute($attribute); // Fallback to original
        }

        $translations = $translation->translations;
        return $translations[$attribute] ?? $this->getAttribute($attribute);
    }

    /**
     * Tạo hoặc cập nhật bản dịch cho một locale
     */
    public function setTranslation(string $locale, array $translations): void
    {
        $this->translations()->updateOrCreate(
            ['locale' => $locale],
            ['translations' => $translations]
        );
    }

    /**
     * Generate bản dịch tự động cho tất cả ngôn ngữ active
     */
    public function generateTranslations(): void
    {
        try {
            $activeLanguages = Language::where('is_active', true)->get();
            $translatedAttributes = $this->translatedAttributes();

            $originalData = [];
            foreach ($translatedAttributes as $attribute) {
                $value = $this->getAttribute($attribute);
                if (!empty($value)) {
                    $originalData[$attribute] = $value;
                }
            }

            if (empty($originalData)) {
                return;
            }

            foreach ($activeLanguages as $language) {
                if ($language->code === config('app.locale', 'vi')) {
                    continue;
                }

                $translations = $this->translateData($originalData, $language->code);

                if (!empty($translations)) {
                    $this->setTranslation($language->code, $translations);
                }
            }
        } catch (\Exception $e) {
            Log::error('Translation generation failed', [
                'model' => get_class($this),
                'id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Dịch text bằng AI
     */
    protected function translateData(array $data, string $targetLocale): ?array
    {
        try {
            $openAIService = app(OpenAIService::class);
            $originalDataJson = json_encode($data, JSON_UNESCAPED_UNICODE);
            $messages = $this->buildTranslationPrompt($originalDataJson, $targetLocale);
            $response = $openAIService->callChat(
                messages: $messages,
                model: OpenAIModel::GPT_4_1_NANO,
                temperature: .2,
                maxTokens: 6000
            );

            if (isset($response->choices[0]->message->content)) {
                $translatedJson = trim($response->choices[0]->message->content);
                $translatedData = json_decode($translatedJson, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($translatedData)) {
                    return $translatedData;
                }

                Log::error('Invalid JSON response from OpenAI', [
                    'locale' => $targetLocale,
                    'response' => $translatedJson
                ]);
            }

            return null;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return null;
        }
    }

    /**
     * Tạo prompt cho việc dịch
     */
    protected function buildTranslationPrompt(string $text, string $targetLocale): array
    {
        $languageNames = [
            'en' => 'English',
            'vi' => 'Vietnamese',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'zh' => 'Chinese',
            'th' => 'Thai',
            'fr' => 'French',
            'de' => 'German',
            'es' => 'Spanish',
            'it' => 'Italian',
            'ru' => 'Russian',
        ];

        $targetLanguage = $languageNames[$targetLocale] ?? $targetLocale;
        $systemPrompt = <<<EOT
            You are a professional translation assistant. Your job is to translate the **values only** of a JSON object into a target language while keeping the keys and structure unchanged.
            Translate fluently and naturally, as if written by a native speaker of the target language. Avoid literal translations; use commonly used and natural phrasing instead. Pay attention to the tone and context of the content (e.g., marketing, UI labels, descriptions).
            Return only a valid JSON object with translated values. Do not add comments, explanations, or anything else outside the JSON object.
        EOT;

        $userPrompt =  "Translate the following JSON to {$targetLanguage}\n\n{$text}";
        return [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];
    }

    /**
     * Kiểm tra có nên tự động dịch không
     */
    protected function shouldAutoTranslate(): bool
    {
        return property_exists($this, 'autoTranslate') ? $this->autoTranslate : true;
    }

    /**
     * Kiểm tra có attribute nào cần dịch bị thay đổi không
     */
    protected function wasTranslatedAttributeChanged(): bool
    {
        $translatedAttributes = $this->translatedAttributes();

        foreach ($translatedAttributes as $attribute) {
            if ($this->wasChanged($attribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Xóa bản dịch cho một locale
     */
    public function deleteTranslation(string $locale): void
    {
        $this->translations()->where('locale', $locale)->delete();
    }

    /**
     * Xóa tất cả bản dịch
     */
    public function deleteAllTranslations(): void
    {
        $this->translations()->delete();
    }

    /**
     * Lấy tất cả bản dịch dạng mảng
     */
    public function getAllTranslations(): array
    {
        $result = [];

        foreach ($this->translations as $translation) {
            $result[$translation->locale] = $translation->translations;
        }

        return $result;
    }

    /**
     * Kiểm tra có bản dịch cho locale không
     */
    public function hasTranslation(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    /**
     * Force regenerate tất cả bản dịch
     */
    public function regenerateTranslations(): void
    {
        $this->deleteAllTranslations();
        $this->generateTranslations();
    }

    /**
     * Abstract method - phải implement trong model sử dụng trait
     * Trả về mảng các attribute cần dịch
     */
    abstract public function translatedAttributes(): array;
}