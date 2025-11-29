<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['image', 'icon', 'services', 'chapter'])->ordered();

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
            $product = $query->where('slug', $request->slug)->first();
            if ($product) {
                return response()->json([
                    'data' => new ProductResource($product),
                ]);
            }
            return response()->json(['message' => 'Продукт не найден'], 404);
        }

        $products = $query->get();

        return response()->json([
            'data' => ProductResource::collection($products),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|array',
            'image_id' => 'nullable|exists:media,id',
            'icon_id' => 'nullable|exists:media,id',
            'chapter_id' => 'nullable|exists:chapters,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
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
            // Проверяем уникальность
            $counter = 1;
            $originalSlug = $data['slug'];
            while (Product::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Определяем order если не указан
        if (!isset($data['order'])) {
            $maxOrder = Product::where('chapter_id', $data['chapter_id'] ?? null)->max('order') ?? -1;
            $data['order'] = $maxOrder + 1;
        }

        $product = Product::create($data);

        // Синхронизируем услуги
        if ($request->has('services')) {
            $product->services()->sync($request->services);
        }

        return response()->json([
            'message' => 'Продукт успешно создан',
            'data' => new ProductResource($product->load(['image', 'icon', 'services', 'chapter'])),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with(['image', 'icon', 'services', 'chapter'])->findOrFail($id);
        
        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $id,
            'description' => 'nullable|array',
            'image_id' => 'nullable|exists:media,id',
            'icon_id' => 'nullable|exists:media,id',
            'chapter_id' => 'nullable|exists:chapters,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
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
        if (empty($data['slug']) && isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = Str::slug($data['name']);
            $counter = 1;
            $originalSlug = $data['slug'];
            while (Product::where('slug', $data['slug'])->where('id', '!=', $id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $product->update($data);

        // Синхронизируем услуги
        if ($request->has('services')) {
            $product->services()->sync($request->services);
        }

        return response()->json([
            'message' => 'Продукт успешно обновлен',
            'data' => new ProductResource($product->load(['image', 'icon', 'services', 'chapter'])),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Продукт успешно удален',
        ]);
    }

    /**
     * Экспортировать продукты в CSV или ZIP
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv'); // По умолчанию CSV
        $export = new ProductsExport();
        
        if ($format === 'zip') {
            return $export->exportToZip();
        }
        
        return $export->exportToCsv();
    }

    /**
     * Импортировать продукты из ZIP архива или CSV
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
        $import = new ProductsImport();
        
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
