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
        'object' => 'required|string|min:1|max:150',
        'heading' => 'required|string|min:1|max:150',
        'img_1' => 'nullable|image|max:1012',
        'img_2' => 'nullable|image|max:1012',
        'body' => 'required|string|min:1',
        'ending' => 'required|string|min:1',
        'sender' => 'required|string|min:1|max:50',
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
                ->simplePaginate(40)
            : new LengthAwarePaginator(collect(), 0, 40);

        return view('admin.Mailer.index', compact('models'));
    }

    public function createModel()
    {
        return view('admin.Customers.mail-model-create');
    }

    public function storeModel(Request $request)
    {
        $request->validate($this->validations);
        $data = $request->all();

        $img1Path = isset($data['img_1'])
            ? Storage::put('public/uploads', $data['img_1'])
            : null;

        $img2Path = isset($data['img_2'])
            ? Storage::put('public/uploads', $data['img_2'])
            : null;

        Model::create([
            'name' => $data['name'],
            'object' => $data['object'],
            'heading' => $data['heading'],
            'body' => $data['body'],
            'ending' => $data['ending'],
            'sender' => $data['sender'],
            'img_1' => $img1Path,
            'img_2' => $img2Path,
        ]);

        $message = 'Il modello "' . $data['name'] . '" è stato creato correttamente';

        return $this->redirectToModels($message);
    }

    public function editModel(int $id)
    {
        $model = Model::query()->findOrFail($id);

        return view('admin.Customers.mail-model-edit', compact('model'));
    }

    public function updateModel(Request $request)
    {
        $request->validate($this->validations);
        $data = $request->all();
        $model = Model::query()->findOrFail((int) $data['id']);

        if (isset($data['img_1'])) {
            $img1Path = Storage::put('public/uploads', $data['img_1']);
            if ($model->img_1) {
                Storage::delete($model->img_1);
            }
            $model->img_1 = $img1Path;
        }

        if (isset($data['img_2'])) {
            $img2Path = Storage::put('public/uploads', $data['img_2']);
            if ($model->img_2) {
                Storage::delete($model->img_2);
            }
            $model->img_2 = $img2Path;
        }

        $model->name = $data['name'];
        $model->object = $data['object'];
        $model->heading = $data['heading'];
        $model->body = $data['body'];
        $model->ending = $data['ending'];
        $model->sender = $data['sender'];
        $model->save();

        $message = 'Il modello "' . $data['name'] . '" è stato modificato correttamente';

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

        return $this->redirectToModels('Modello eliminato con successo');
    }

    private function redirectToModels(string $message)
    {
        return redirect()
            ->route('admin.customers.mail_models.index')
            ->with('success', $message);
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
