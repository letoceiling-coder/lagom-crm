<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Exports\ServicesExport;
use App\Imports\ServicesImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Service::with(['image', 'icon', 'products', 'chapter'])->ordered();

        if ($request->has('chapter_id')) {
            $query->where('chapter_id', $request->chapter_id);
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        } else {
            $query->active();
        }

        // Поиск по slug или id
        if ($request->has('slug')) {
            $service = $query->where('slug', $request->slug)->first();
            if ($service) {
                return response()->json([
                    'data' => new ServiceResource($service),
                ]);
            }
            return response()->json(['message' => 'Услуга не найдена'], 404);
        }

        $services = $query->get();

        return response()->json([
            'data' => ServiceResource::collection($services),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:services,slug',
            'description' => 'nullable|array',
            'image_id' => 'nullable|exists:media,id',
            'icon_id' => 'nullable|exists:media,id',
            'chapter_id' => 'nullable|exists:chapters,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->only([
            'name',
            'description',
            'image_id',
            'icon_id',
            'chapter_id',
            'order',
            'is_active',
        ]);

        // Генерируем slug если не указан
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
            $counter = 1;
            $originalSlug = $data['slug'];
            while (Service::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Определяем order если не указан
        if (!isset($data['order'])) {
            $maxOrder = Service::where('chapter_id', $data['chapter_id'] ?? null)->max('order') ?? -1;
            $data['order'] = $maxOrder + 1;
        }

        $service = Service::create($data);

        // Синхронизируем продукты
        if ($request->has('products')) {
            $service->products()->sync($request->products);
        }

        return response()->json([
            'message' => 'Услуга успешно создана',
            'data' => new ServiceResource($service->load(['image', 'icon', 'products', 'chapter'])),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $service = Service::with(['image', 'icon', 'products', 'chapter'])->findOrFail($id);
        
        return response()->json([
            'data' => new ServiceResource($service),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $service = Service::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:services,slug,' . $id,
            'description' => 'nullable|array',
            'image_id' => 'nullable|exists:media,id',
            'icon_id' => 'nullable|exists:media,id',
            'chapter_id' => 'nullable|exists:chapters,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->only([
            'name',
            'slug',
            'description',
            'image_id',
            'icon_id',
            'chapter_id',
            'order',
            'is_active',
        ]);

        // Генерируем slug если не указан и имя изменилось
        if (empty($data['slug']) && isset($data['name']) && $data['name'] !== $service->name) {
            $data['slug'] = Str::slug($data['name']);
            $counter = 1;
            $originalSlug = $data['slug'];
            while (Service::where('slug', $data['slug'])->where('id', '!=', $id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $service->update($data);

        // Синхронизируем продукты
        if ($request->has('products')) {
            $service->products()->sync($request->products);
        }

        return response()->json([
            'message' => 'Услуга успешно обновлена',
            'data' => new ServiceResource($service->load(['image', 'icon', 'products', 'chapter'])),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json([
            'message' => 'Услуга успешно удалена',
        ]);
    }

    /**
     * Экспортировать услуги в CSV или ZIP
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv'); // По умолчанию CSV
        $export = new ServicesExport();
        
        if ($format === 'zip') {
            return $export->exportToZip();
        }
        
        return $export->exportToCsv();
    }

    /**
     * Импортировать услуги из ZIP архива или CSV
     */
    public function import(Request $request)
    {
        // Проверяем наличие файла
        if (!$request->hasFile('file')) {
            return response()->json([
                'message' => 'Файл не был загружен. Возможно, файл слишком большой.',
                'errors' => ['Максимальный размер файла: 100MB. Проверьте настройки PHP (upload_max_filesize, post_max_size) и веб-сервера.'],
            ], 422);
        }

        $request->validate([
            'file' => 'required|file|mimes:zip,csv,txt|max:102400', // 100MB для ZIP
        ], [
            'file.max' => 'Размер файла не должен превышать 100MB. Текущий размер: :max KB',
            'file.mimes' => 'Поддерживаются только файлы: zip, csv, txt',
        ]);

        $file = $request->file('file');
        
        // Проверяем размер файла
        $fileSize = $file->getSize();
        $maxSize = 102400 * 1024; // 100MB в байтах
        
        if ($fileSize > $maxSize) {
            return response()->json([
                'message' => 'Файл слишком большой',
                'errors' => [
                    'Размер файла: ' . round($fileSize / 1024 / 1024, 2) . ' MB',
                    'Максимальный размер: 100 MB',
                ],
            ], 422);
        }
        $import = new ServicesImport();
        
        // Определяем тип файла
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Если это ZIP архив
        if ($extension === 'zip' || in_array($mimeType, ['application/zip', 'application/x-zip-compressed'])) {
            $result = $import->importFromZip($file);
        } else {
            // Если это CSV
            $result = $import->importFromCsv($file);
        }

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? [],
            ], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'success_count' => $result['success_count'],
            'skip_count' => $result['skip_count'],
            'errors' => $result['errors'],
        ]);
    }
}
