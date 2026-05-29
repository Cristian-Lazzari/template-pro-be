<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MailerController extends Controller
{
    private array $validations = [
        'name' => 'required|string|min:1|max:50',
        'object' => 'nullable|string|max:150',
        'heading' => 'nullable|string|max:150',
        'img_1' => 'nullable|image|max:1012',
        'img_2' => 'nullable|image|max:1012',
        'body' => 'nullable|string',
        'body_html' => 'nullable|string',
        'body_text' => 'nullable|string',
        'ending' => 'nullable|string',
        'sender' => 'nullable|string|max:50',
        'status' => 'nullable|in:draft,active,archived',
        'has_promotion' => 'nullable|boolean',
    ];

    private array $supportedVariables = [
        'customer_name',
        'customer_first_name',
        'customer_last_name',
        'customer_email',
        'customer_phone',
        'customer_age',
        'customer_gender',
    ];

    public function indexModels()
    {
        $countRelations = array_filter([
            Schema::hasTable('campaigns') ? 'campaigns' : null,
            Schema::hasTable('automations') ? 'automations' : null,
        ]);

        $models = Schema::hasTable('models')
            ? $this->mailModelQuery()
                ->when($countRelations !== [], fn ($query) => $query->withCount($countRelations))
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->paginate(40)
            : new LengthAwarePaginator(collect(), 0, 40);

        return view('admin.Mailer.index', compact('models'));
    }

    public function showModel(int $id)
    {
        $countRelations = array_filter([
            Schema::hasTable('campaigns') ? 'campaigns' : null,
            Schema::hasTable('automations') ? 'automations' : null,
        ]);

        $model = Model::query()
            ->when($countRelations !== [], fn ($query) => $query->withCount($countRelations))
            ->findOrFail($id);

        return view('admin.Mailer.show', [
            'model' => $model,
            'previewData' => $this->previewData(),
        ]);
    }

    public function createModel()
    {
        return view('admin.Customers.mail-model-create', [
            'model' => new Model([
                'status' => 'draft',
                'type' => 'marketing',
                'channel' => 'email',
            ]),
            'variables' => $this->supportedVariables,
        ]);
    }

    public function storeModel(Request $request)
    {
        $data = $request->validate($this->validations);

        $img1Path = $request->hasFile('img_1')
            ? Storage::put('public/uploads', $request->file('img_1'))
            : null;

        $img2Path = $request->hasFile('img_2')
            ? Storage::put('public/uploads', $request->file('img_2'))
            : null;

        Model::create(array_merge($this->modelPayload($data), [
            'img_1' => $img1Path,
            'img_2' => $img2Path,
        ]));

        $message = __('admin.marketing.mailer.created_flash', ['name' => $data['name']]);

        return $this->redirectToModels($message);
    }

    public function editModel(int $id)
    {
        $model = Model::query()->findOrFail($id);

        return view('admin.Customers.mail-model-edit', [
            'model' => $model,
            'variables' => $this->supportedVariables,
        ]);
    }

    public function updateModel(Request $request)
    {
        $data = $request->validate(array_merge($this->validations, [
            'id' => 'required|integer|exists:models,id',
        ]));
        $model = Model::query()->findOrFail((int) $data['id']);

        if ($request->hasFile('img_1')) {
            $img1Path = Storage::put('public/uploads', $request->file('img_1'));
            if ($model->img_1) {
                Storage::delete($model->img_1);
            }
            $model->img_1 = $img1Path;
        }

        if ($request->hasFile('img_2')) {
            $img2Path = Storage::put('public/uploads', $request->file('img_2'));
            if ($model->img_2) {
                Storage::delete($model->img_2);
            }
            $model->img_2 = $img2Path;
        }

        $model->fill($this->modelPayload($data));
        $model->save();

        $message = __('admin.marketing.mailer.updated_flash', ['name' => $data['name']]);

        return $this->redirectToModels($message);
    }

    public function deleteModel(int $id)
    {
        $model = Model::query()->findOrFail($id);

        if ($model->img_1) {
            Storage::delete($model->img_1);
        }

        if ($model->img_2) {
            Storage::delete($model->img_2);
        }

        $model->delete();

        return $this->redirectToModels(__('admin.marketing.mailer.deleted_flash'));
    }

    private function redirectToModels(string $message)
    {
        return redirect()
            ->route('admin.customers.mail_models.index')
            ->with('success', $message);
    }

    private function modelPayload(array $data): array
    {
        $bodyHtml = trim((string) ($data['body_html'] ?? ''));
        $body = trim((string) ($data['body'] ?? ''));
        $bodyText = trim((string) ($data['body_text'] ?? ''));

        if ($bodyHtml === '' && $body !== '') {
            $bodyHtml = $body;
        }

        if ($body === '' && $bodyHtml !== '') {
            $body = $bodyHtml;
        }

        if ($bodyText === '' && $bodyHtml !== '') {
            $bodyText = trim(html_entity_decode(strip_tags($bodyHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        $payload = [
            'name' => $data['name'],
            'object' => trim((string) ($data['object'] ?? '')),
            'heading' => trim((string) ($data['heading'] ?? '')),
            'body' => $body,
            'ending' => trim((string) ($data['ending'] ?? '')),
            'sender' => trim((string) ($data['sender'] ?? '')),
        ];

        $marketingPayload = [
            'type' => 'marketing',
            'channel' => 'email',
            'status' => $data['status'] ?? 'draft',
            'has_promotion' => (bool) ($data['has_promotion'] ?? false),
            'body_html' => $bodyHtml,
            'body_text' => $bodyText !== '' ? $bodyText : null,
            'variables' => $this->supportedVariables,
            'preview_data' => $this->previewData(),
        ];

        foreach ($marketingPayload as $column => $value) {
            if (Schema::hasColumn('models', $column)) {
                $payload[$column] = $value;
            }
        }

        return $payload;
    }

    private function previewData(): array
    {
        return [
            'customer_name'       => 'Mario Rossi',
            'customer_first_name' => 'Mario',
            'customer_last_name'  => 'Rossi',
            'customer_email'      => 'mario@example.com',
            'customer_phone'      => '+39 333 000 0000',
            'customer_age'        => '42',
            'customer_gender'     => 'Uomo',
        ];
    }

    private function mailModelQuery()
    {
        $hasType = Schema::hasColumn('models', 'type');
        $hasChannel = Schema::hasColumn('models', 'channel');

        return Model::query()
            ->when($hasType || $hasChannel, function ($query) use ($hasType, $hasChannel) {
                $query->where(function ($nested) use ($hasType, $hasChannel) {
                    if ($hasType) {
                        $nested->orWhere('type', 'marketing');
                    }

                    if ($hasChannel) {
                        $nested->orWhere('channel', 'email');
                    }
                });
            });
    }
}
