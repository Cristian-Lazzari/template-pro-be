<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePromotionRequest;
use App\Http\Requests\Admin\UpdatePromotionRequest;
use App\Models\Promotion;
use App\Models\PromotionTarget;
use App\Services\Marketing\MarketingReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PromotionController extends Controller
{
    private const STATUSES = [
        'draft' => 'Bozza',
        'active' => 'Attiva',
        'paused' => 'In pausa',
        'archived' => 'Archiviata',
    ];

    private const CASE_USES = [
        'generic' => 'Generica',
        'take_away' => 'Asporto',
        'delivery' => 'Delivery',
        'table' => 'Tavolo',
        'gift' => 'Gift',
    ];

    private const DISCOUNT_TYPES = [
        'fixed' => 'Importo fisso',
        'percentage' => 'Percentuale',
        'gift' => 'Omaggio',
    ];

    private const TARGET_TYPES = [
        'generic' => 'Carrello generico',
        'product' => 'Prodotto',
        'menu' => 'Menu',
        'category' => 'Categoria',
        'post' => 'Post',
    ];

    private const FORM_TARGET_TYPES = [
        'product' => 'Prodotto',
        'category' => 'Categoria',
        'menu' => 'Menu',
    ];

    public function index()
    {
        $promotions = Promotion::query()
            ->withCount('targets')
            ->orderBy('updated_at', 'desc')
            ->simplePaginate(40);

        return view('admin.Promotions.index', [
            'promotions' => $promotions,
            'statuses' => self::STATUSES,
            'caseUses' => self::CASE_USES,
            'discountTypes' => self::DISCOUNT_TYPES,
        ]);
    }

    public function create()
    {
        $promotion = new Promotion([
            'status' => 'draft',
            'permanent' => false,
            'metadata' => ['reusable' => false],
        ]);

        return view('admin.Promotions.create', array_merge(
            $this->formOptions($promotion),
            compact('promotion')
        ));
    }

    public function store(StorePromotionRequest $request)
    {
        $promotion = DB::transaction(function () use ($request) {
            $promotion = Promotion::query()->create(
                $this->promotionData($request)
            );

            $this->syncTargets($promotion, $request);

            return $promotion;
        });

        return to_route('admin.promotions.show', $promotion)
            ->with('success', 'Promozione creata correttamente.');
    }

    public function show(Promotion $promotion, MarketingReportService $reportService)
    {
        $promotion->load('targets');
        $report = $reportService->forPromotion($promotion);
        $targetOptions = $this->targetOptions($promotion);

        return view('admin.Promotions.show', [
            'promotion' => $promotion,
            'report' => $report,
            'statuses' => self::STATUSES,
            'caseUses' => self::CASE_USES,
            'discountTypes' => self::DISCOUNT_TYPES,
            'targetTypes' => self::TARGET_TYPES,
            'targetLabels' => $this->targetLabels($targetOptions),
        ]);
    }

    public function edit(Promotion $promotion)
    {
        $promotion->load('targets');

        return view('admin.Promotions.edit', array_merge(
            $this->formOptions($promotion),
            compact('promotion')
        ));
    }

    public function update(UpdatePromotionRequest $request, Promotion $promotion)
    {
        DB::transaction(function () use ($request, $promotion) {
            $promotion->update(
                $this->promotionData($request, $promotion)
            );

            $this->syncTargets($promotion, $request);
        });

        return to_route('admin.promotions.show', $promotion)
            ->with('success', 'Promozione aggiornata correttamente.');
    }

    public function publish(Promotion $promotion)
    {
        return $this->updateStatus($promotion, 'active', 'Promozione pubblicata correttamente.');
    }

    public function pause(Promotion $promotion)
    {
        return $this->updateStatus($promotion, 'paused', 'Promozione messa in pausa correttamente.');
    }

    public function archive(Promotion $promotion)
    {
        return $this->updateStatus($promotion, 'archived', 'Promozione archiviata correttamente.');
    }

    public function draft(Promotion $promotion)
    {
        return $this->updateStatus($promotion, 'draft', 'Promozione salvata come bozza.');
    }

    private function formOptions(?Promotion $promotion = null): array
    {
        $targetOptions = $this->targetOptions($promotion);
        $specificTargetOptions = $this->specificTargetOptions($promotion);

        return [
            'statuses' => self::STATUSES,
            'caseUses' => self::CASE_USES,
            'discountTypes' => self::DISCOUNT_TYPES,
            'targetTypes' => self::TARGET_TYPES,
            'formTargetTypes' => self::FORM_TARGET_TYPES,
            'targetOptions' => $targetOptions,
            'specificTargetOptions' => $specificTargetOptions,
            'targetLabels' => $this->targetLabels($targetOptions),
        ];
    }

    private function promotionData(Request $request, ?Promotion $promotion = null): array
    {
        $data = $request->validated();
        $metadata = is_array($promotion?->metadata) ? $promotion->metadata : [];

        $slug = trim((string) ($data['slug'] ?? ''));
        $data['slug'] = $this->uniqueSlug($slug !== '' ? $slug : $data['name'], $promotion?->getKey());
        $data['permanent'] = $request->boolean('permanent');
        if ($request->input('target_scope', 'generic') === 'specific') {
            $data['type_discount'] = null;
            $data['discount'] = null;
        }
        $metadata['reusable'] = $request->boolean('metadata.reusable');
        $data['metadata'] = $metadata;
        unset($data['targets'], $data['target_scope']);

        if ($promotion?->exists) {
            unset($data['status']);
        } else {
            $data['status'] = 'draft';
        }

        return $data;
    }

    private function syncTargets(Promotion $promotion, Request $request): void
    {
        $targets = $this->targetData($request, $promotion);

        $promotion->targets()->delete();

        if ($targets !== []) {
            $promotion->targets()->createMany($targets);
        }
    }

    private function targetData(Request $request, ?Promotion $promotion = null): array
    {
        if ($request->input('target_scope', 'generic') !== 'specific') {
            return [
                [
                    'target_type' => PromotionTarget::TYPE_GENERIC,
                    'target_id' => null,
                    'discount' => null,
                    'type_discount' => null,
                    'metadata' => null,
                ],
            ];
        }

        $allowedTargetKeys = array_flip(array_keys($this->targetLabels($this->specificTargetOptions($promotion))));
        $seen = [];
        $targets = [];

        foreach ((array) $request->input('targets', []) as $row) {
            $targetKey = trim((string) ($row['target_key'] ?? ''));

            if ($targetKey === '' || ! isset($allowedTargetKeys[$targetKey])) {
                continue;
            }

            [$targetType, $targetId] = array_pad(explode(':', $targetKey, 2), 2, null);

            if (! array_key_exists($targetType, self::TARGET_TYPES)) {
                continue;
            }

            $targetId = $targetId !== null && $targetId !== '' ? (int) $targetId : null;

            if (! array_key_exists($targetType, self::FORM_TARGET_TYPES) || ! $targetId) {
                continue;
            }

            $uniqueKey = $targetType . ':' . ($targetId ?? '');

            if (isset($seen[$uniqueKey])) {
                continue;
            }

            $seen[$uniqueKey] = true;

            $targets[] = [
                'target_type' => $targetType,
                'target_id' => $targetId,
                'discount' => $this->nullableNumber($row['discount'] ?? null),
                'type_discount' => ($row['type_discount'] ?? '') !== '' ? $row['type_discount'] : null,
                'metadata' => null,
            ];
        }

        return $targets;
    }

    private function nullableNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function targetOptions(?Promotion $promotion = null): array
    {
        $options = [
            PromotionTarget::TYPE_GENERIC => [
                ['key' => PromotionTarget::TYPE_GENERIC . ':', 'label' => 'Generico'],
            ],
            PromotionTarget::TYPE_PRODUCT => $this->translatedTargetOptions(
                PromotionTarget::TYPE_PRODUCT,
                'products',
                'product_translations',
                'product_id',
                'Prodotto'
            ),
            PromotionTarget::TYPE_MENU => $this->translatedTargetOptions(
                PromotionTarget::TYPE_MENU,
                'menus',
                'menu_translations',
                'menu_id',
                'Menu'
            ),
            PromotionTarget::TYPE_CATEGORY => $this->translatedTargetOptions(
                PromotionTarget::TYPE_CATEGORY,
                'categories',
                'category_translations',
                'category_id',
                'Categoria'
            ),
            PromotionTarget::TYPE_POST => $this->postTargetOptions(),
        ];

        if ($promotion?->exists) {
            foreach ($promotion->targets as $target) {
                $this->appendTargetOption($options, $target);
            }
        }

        return $options;
    }

    private function specificTargetOptions(?Promotion $promotion = null): array
    {
        $allOptions = $this->targetOptions($promotion);

        return array_intersect_key($allOptions, self::FORM_TARGET_TYPES);
    }

    private function translatedTargetOptions(
        string $type,
        string $table,
        string $translationTable,
        string $foreignKey,
        string $fallbackLabel
    ): array {
        $locale = app()->getLocale();
        $defaultLocale = 'en';

        $query = DB::table($table)
            ->leftJoin($translationTable . ' as current_translation', function ($join) use ($table, $foreignKey, $locale) {
                $join->on($table . '.id', '=', 'current_translation.' . $foreignKey)
                    ->where('current_translation.lang', $locale);
            })
            ->leftJoin($translationTable . ' as default_translation', function ($join) use ($table, $foreignKey, $defaultLocale) {
                $join->on($table . '.id', '=', 'default_translation.' . $foreignKey)
                    ->where('default_translation.lang', $defaultLocale);
            })
            ->select(
                $table . '.id',
                'current_translation.name as current_name',
                'default_translation.name as default_name'
            )
            ->orderBy($table . '.updated_at', 'desc')
            ->limit(500);

        if ($table === 'products') {
            $query->where($table . '.archived', false);
        }

        if ($table === 'menus') {
            $query->where($table . '.visible', true);
        }

        return $query
            ->get()
            ->map(fn ($row) => [
                'key' => $type . ':' . $row->id,
                'label' => '#' . $row->id . ' - ' . ($row->current_name ?: $row->default_name ?: $fallbackLabel),
            ])
            ->all();
    }

    private function postTargetOptions(): array
    {
        return DB::table('posts')
            ->select('id', 'title')
            ->where('archived', false)
            ->orderBy('updated_at', 'desc')
            ->limit(500)
            ->get()
            ->map(fn ($row) => [
                'key' => PromotionTarget::TYPE_POST . ':' . $row->id,
                'label' => '#' . $row->id . ' - ' . ($row->title ?: 'Post'),
            ])
            ->all();
    }

    private function appendTargetOption(array &$options, PromotionTarget $target): void
    {
        $group = $target->target_type;
        $key = $group . ':' . ($target->target_id ?? '');

        if (! array_key_exists($group, $options)) {
            return;
        }

        foreach ($options[$group] as $option) {
            if ($option['key'] === $key) {
                return;
            }
        }

        $options[$group][] = [
            'key' => $key,
            'label' => $this->targetOptionLabel($target),
        ];
    }

    private function targetOptionLabel(PromotionTarget $target): string
    {
        if ($target->isGenericTarget()) {
            return 'Generico';
        }

        $model = $target->target();
        $label = $model?->name ?? $model?->title ?? null;

        return '#' . $target->target_id . ' - ' . ($label ?: (self::TARGET_TYPES[$target->target_type] ?? 'Target'));
    }

    private function targetLabels(array $targetOptions): array
    {
        $labels = [];

        foreach ($targetOptions as $groupOptions) {
            foreach ($groupOptions as $option) {
                $labels[$option['key']] = $option['label'];
            }
        }

        return $labels;
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = 'promotion';
        }

        $slug = $baseSlug;
        $suffix = 2;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Promotion::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    private function updateStatus(Promotion $promotion, string $status, string $message)
    {
        $promotion->update(['status' => $status]);

        return back()->with('success', $message);
    }
}
