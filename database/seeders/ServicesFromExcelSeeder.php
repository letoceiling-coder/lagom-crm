<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Service;
use App\Models\Chapter;
use App\Models\ProjectCase;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ServicesFromExcelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем путь к файлу из .env или используем стандартные пути
        $excelPath = env('SERVICES_EXCEL_PATH') ?? $this->findExcelFile();
        
        // Для локальной разработки - проверяем стандартный путь Windows
        if (!$excelPath || !file_exists($excelPath)) {
            $windowsPath = 'C:\Users\dsc-2\Downloads\Telegram Desktop\feed.xlsx';
            if (file_exists($windowsPath)) {
                $excelPath = $windowsPath;
            }
        }
        
        if (!$excelPath || !file_exists($excelPath)) {
            $this->command->error("Файл не найден!");
            $this->command->info("Укажите путь к файлу через .env (SERVICES_EXCEL_PATH)");
            $this->command->info("Или поместите файл feed.xlsx в одну из стандартных директорий:");
            $this->command->info("  - " . base_path('feed.xlsx'));
            $this->command->info("  - " . base_path('storage/app/feed.xlsx'));
            $this->command->info("  - C:\\Users\\dsc-2\\Downloads\\Telegram Desktop\\feed.xlsx (локально)");
            return;
        }
        
        $this->command->info("Используется файл: {$excelPath}");

        $this->command->info("Чтение Excel файла...");
        
        try {
            $spreadsheet = IOFactory::load($excelPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Пропускаем заголовок (первая строка)
            array_shift($rows);
            
            $this->command->info("Найдено строк: " . count($rows));
            
            $currentService = null;
            $currentChapter = null;
            $currentChapterId = null;
            $order = 0;
            $chapterOrder = 0;
            $caseOrder = 0;
            
            $stats = [
                'services' => 0,
                'chapters' => 0,
                'cases' => 0,
            ];
            
            foreach ($rows as $rowIndex => $row) {
                // Обрабатываем значения, заменяя NaN и пустые строки
                $serviceName = $this->cleanValue($row[0] ?? '');
                $chapterName = $this->cleanValue($row[1] ?? '');
                $caseName = $this->cleanValue($row[2] ?? '');
                $description = $this->cleanValue($row[3] ?? '');
                $htmlText = $this->cleanValue($row[4] ?? '');
                $detailedText = $this->cleanValue($row[5] ?? '');
                
                // Если есть название услуги - создаем новую услугу
                if (!empty($serviceName) && $serviceName !== 'NaN') {
                    $currentService = $this->createOrUpdateService($serviceName, $description, $htmlText, $detailedText, $order);
                    $order++;
                    $stats['services']++;
                    $this->command->info("✓ Услуга: {$serviceName}");
                }
                
                // Если есть название раздела - создаем/находим раздел
                if (!empty($chapterName) && $chapterName !== 'NaN') {
                    $currentChapter = $this->createOrUpdateChapter($chapterName, $chapterOrder);
                    $currentChapterId = $currentChapter->id;
                    $chapterOrder++;
                    $caseOrder = 0; // Сбрасываем порядок случаев для нового раздела
                    $stats['chapters']++;
                    $this->command->info("  → Раздел: {$chapterName}");
                }
                
                // Если есть название случая - создаем случай и связываем с разделом
                if (!empty($caseName) && $caseName !== 'NaN' && $currentChapterId) {
                    $this->createOrUpdateCase($caseName, $currentChapterId, $description, $htmlText, $detailedText, $caseOrder);
                    $caseOrder++;
                    $stats['cases']++;
                    $this->command->info("    • Случай: {$caseName}");
                }
            }
            
            $this->command->info("\n📊 Статистика импорта:");
            $this->command->info("  Услуг: {$stats['services']}");
            $this->command->info("  Разделов: {$stats['chapters']}");
            $this->command->info("  Случаев: {$stats['cases']}");
            
            $this->command->info("Импорт завершен успешно!");
            
        } catch (\Exception $e) {
            $this->command->error("Ошибка при импорте: " . $e->getMessage());
            $this->command->error($e->getTraceAsString());
        }
    }
    
    private function createOrUpdateService($name, $description, $htmlText, $detailedText, $order)
    {
        // Обрезаем название до 255 символов
        $name = mb_substr($name, 0, 255);
        
        $slug = Str::slug($name);
        
        // Обрезаем slug до 255 символов
        if (mb_strlen($slug) > 255) {
            $slug = mb_substr($slug, 0, 252) . '-' . substr(md5($name), 0, 2);
        }
        
        // Проверяем уникальность slug
        $counter = 1;
        $originalSlug = $slug;
        while (Service::where('slug', $slug)->exists()) {
            $slug = mb_substr($originalSlug, 0, 250) . '-' . $counter;
            $counter++;
        }
        
        $descriptionData = [];
        if (!empty($description)) {
            $descriptionData['ru'] = $description;
        }
        if (!empty($detailedText)) {
            $descriptionData['detailed'] = $detailedText;
        }
        
        return Service::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'slug' => $slug,
                'description' => !empty($descriptionData) ? $descriptionData : null,
                'order' => $order,
                'is_active' => true,
            ]
        );
    }
    
    private function createOrUpdateChapter($name, $order)
    {
        return Chapter::firstOrCreate(
            ['name' => $name],
            [
                'name' => $name,
                'order' => $order,
                'is_active' => true,
            ]
        );
    }
    
    private function createOrUpdateCase($name, $chapterId, $description, $htmlText, $detailedText, $order)
    {
        // Обрезаем название до 255 символов (стандартный размер VARCHAR)
        $name = mb_substr($name, 0, 255);
        
        $slug = Str::slug($name);
        
        // Обрезаем slug до 255 символов
        if (mb_strlen($slug) > 255) {
            $slug = mb_substr($slug, 0, 252) . '-' . substr(md5($name), 0, 2);
        }
        
        // Проверяем уникальность slug
        $counter = 1;
        $originalSlug = $slug;
        while (ProjectCase::where('slug', $slug)->exists()) {
            $slug = mb_substr($originalSlug, 0, 250) . '-' . $counter;
            $counter++;
        }
        
        $descriptionData = [];
        if (!empty($description)) {
            $descriptionData['ru'] = $description;
        }
        if (!empty($detailedText)) {
            $descriptionData['detailed'] = $detailedText;
        }
        
        $htmlData = null;
        if (!empty($htmlText)) {
            $htmlData = ['content' => $htmlText];
        }
        
        return ProjectCase::updateOrCreate(
            [
                'name' => $name,
                'chapter_id' => $chapterId,
            ],
            [
                'name' => $name,
                'slug' => $slug,
                'description' => !empty($descriptionData) ? $descriptionData : null,
                'html' => $htmlData,
                'chapter_id' => $chapterId,
                'order' => $order,
                'is_active' => true,
            ]
        );
    }
    
    /**
     * Найти файл Excel в стандартных местах
     */
    private function findExcelFile(): ?string
    {
        $possiblePaths = [
            base_path('feed.xlsx'),
            base_path('storage/app/feed.xlsx'),
            base_path('database/seeders/feed.xlsx'),
            '/home/d/dsc23ytp/stroy/public_html/feed.xlsx',
            '/home/d/dsc23ytp/stroy/public_html/storage/app/feed.xlsx',
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Очистить значение от NaN и пустых строк
     */
    private function cleanValue($value): string
    {
        if (is_null($value) || $value === 'NaN' || $value === 'nan' || (is_string($value) && strtolower(trim($value)) === 'nan')) {
            return '';
        }
        
        if (is_float($value) && is_nan($value)) {
            return '';
        }
        
        return trim((string)$value);
    }
}

