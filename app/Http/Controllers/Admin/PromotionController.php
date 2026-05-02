<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePromotionRequest;
use App\Http\Requests\Admin\UpdatePromotionRequest;
use App\Models\Promotion;
use App\Services\Marketing\MarketingReportService;
use Illuminate\Http\Request;
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

    public function index()
    {
        $promotions = Promotion::query()
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
            $this->formOptions(),
            compact('promotion')
        ));
    }

    public function store(StorePromotionRequest $request)
    {
        $promotion = Promotion::query()->create(
            $this->promotionData($request)
        );

        return to_route('admin.promotions.show', $promotion)
            ->with('success', 'Promozione creata correttamente.');
    }

    public function show(Promotion $promotion, MarketingReportService $reportService)
    {
        $report = $reportService->forPromotion($promotion);

        return view('admin.Promotions.show', [
            'promotion' => $promotion,
            'report' => $report,
            'statuses' => self::STATUSES,
            'caseUses' => self::CASE_USES,
            'discountTypes' => self::DISCOUNT_TYPES,
        ]);
    }

    public function edit(Promotion $promotion)
    {
        return view('admin.Promotions.edit', array_merge(
            $this->formOptions(),
            compact('promotion')
        ));
    }

    public function update(UpdatePromotionRequest $request, Promotion $promotion)
    {
        $promotion->update(
            $this->promotionData($request, $promotion)
        );

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

    private function formOptions(): array
    {
        return [
            'statuses' => self::STATUSES,
            'caseUses' => self::CASE_USES,
            'discountTypes' => self::DISCOUNT_TYPES,
        ];
    }

    private function promotionData(Request $request, ?Promotion $promotion = null): array
    {
        $data = $request->validated();
        $metadata = is_array($promotion?->metadata) ? $promotion->metadata : [];

        $slug = trim((string) ($data['slug'] ?? ''));
        $data['slug'] = $this->uniqueSlug($slug !== '' ? $slug : $data['name'], $promotion?->getKey());
        $data['permanent'] = $request->boolean('permanent');
        $metadata['reusable'] = $request->boolean('metadata.reusable');
        $data['metadata'] = $metadata;

        return $data;
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
