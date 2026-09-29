<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Faker\Factory as FakerFactory;
use Faker\Generator as Faker;

// Загрузка .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "🌱 Starting database seeding...\n\n";

try {
    // Подключение к БД
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']};charset=utf8mb4",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    // Явно устанавливаем кодировку после подключения
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("SET character_set_client = utf8mb4");
    $pdo->exec("SET character_set_connection = utf8mb4");
    $pdo->exec("SET character_set_results = utf8mb4");

    // Инициализация Faker
    $faker = FakerFactory::create('ru_RU');

    // Очистка таблиц (в правильном порядке из-за внешних ключей)
    echo "🧹 Clearing existing data...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE post_categories");
    $pdo->exec("TRUNCATE TABLE posts");
    $pdo->exec("TRUNCATE TABLE categories");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "✅ Tables cleared\n\n";

    // ===========================================
    // Генерация категорий
    // ===========================================
    echo "📂 Creating categories...\n";
    
    $categoryNames = [
        'Технологии' => 'Новости из мира IT, программирования и технологий',
        'Дизайн' => 'Веб-дизайн, UI/UX, графический дизайн и тренды',
        'Маркетинг' => 'Digital-маркетинг, SMM, контент-маркетинг',
        'Бизнес' => 'Стартапы, предпринимательство, управление проектами',
        'Наука' => 'Научные открытия, исследования, космос',
        'Путешествия' => 'Истории путешествий, советы туристам, обзоры стран',
        'Кулинария' => 'Рецепты, обзоры ресторанов, кулинарные техники',
        'Спорт' => 'Новости спорта, тренировки, здоровый образ жизни',
    ];

    $categories = [];
    $stmtCategory = $pdo->prepare("
        INSERT INTO categories (name, description, slug, created_at) 
        VALUES (:name, :description, :slug, :created_at)
    ");

    foreach ($categoryNames as $name => $description) {
        $slug = transliterate($name);
        $createdAt = $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s');
        
        $stmtCategory->execute([
            'name' => $name,
            'description' => $description,
            'slug' => $slug,
            'created_at' => $createdAt,
        ]);
        
        $categoryId = (int) $pdo->lastInsertId();
        $categories[$categoryId] = $name;
        
        echo "  ✓ {$name}\n";
    }
    
    echo "✅ Created " . count($categories) . " categories\n\n";

    // ===========================================
    // Генерация статей
    // ===========================================
    echo "📝 Creating posts...\n";
    
    $postsCount = 50;
    $categoryIds = array_keys($categories);
    $posts = [];
    
    $stmtPost = $pdo->prepare("
        INSERT INTO posts (image, title, description, content, views, slug, created_at) 
        VALUES (:image, :title, :description, :content, :views, :slug, :created_at)
    ");

    $pdo->beginTransaction();

    for ($i = 1; $i <= $postsCount; $i++) {
        $title = $faker->sentence(6);
        $description = $faker->paragraph(2);
        $content = generateArticleContent($faker);
        $views = $faker->numberBetween(0, 10000);
        $slug = transliterate($title) . '-' . $i;
        $createdAt = $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s');
        $image = "https://picsum.photos/800/400?random={$i}"; // Placeholder изображения
        
        $stmtPost->execute([
            'image' => $image,
            'title' => $title,
            'description' => $description,
            'content' => $content,
            'views' => $views,
            'slug' => $slug,
            'created_at' => $createdAt,
        ]);
        
        $postId = (int) $pdo->lastInsertId();
        $posts[] = $postId;
        
        if ($i % 10 === 0) {
            echo "  ✓ Generated {$i} posts...\n";
        }
    }

    $pdo->commit();
    echo "✅ Created {$postsCount} posts\n\n";

    // ===========================================
    // Привязка статей к категориям
    // ===========================================
    echo "🔗 Linking posts to categories...\n";
    
    $stmtLink = $pdo->prepare("
        INSERT INTO post_categories (post_id, category_id) 
        VALUES (:post_id, :category_id)
    ");

    $pdo->beginTransaction();

    foreach ($posts as $postId) {
        // Каждая статья привязана к 1-3 случайным категориям
        $categoriesCount = $faker->numberBetween(1, 3);
        $selectedCategories = (array) $faker->randomElements($categoryIds, $categoriesCount);
        
        foreach ($selectedCategories as $categoryId) {
            $stmtLink->execute([
                'post_id' => $postId,
                'category_id' => $categoryId,
            ]);
        }
    }

    $pdo->commit();
    echo "✅ Linked posts to categories\n\n";

    // ===========================================
    // Статистика
    // ===========================================
    echo "📊 Statistics:\n";
    echo "  Categories: " . count($categories) . "\n";
    echo "  Posts: " . count($posts) . "\n";
    
    $stmtCount = $pdo->query("SELECT COUNT(*) AS total FROM post_categories");
    $linksCount = $stmtCount->fetch()['total'];
    echo "  Category-Post links: {$linksCount}\n";
    
    echo "\n✅ Seeding completed successfully!\n";

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// ===========================================
// Вспомогательные функции
// ===========================================

/**
 * Простая транслитерация для slug.
 */
function transliterate(string $text): string
{
    $map = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
        'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
        'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
        'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
        'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
        'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts', 'Ч' => 'Ch',
        'Ш' => 'Sh', 'Щ' => 'Sch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '',
        'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
    ];
    
    $text = strtr($text, $map);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    $text = trim($text, '-');
    
    return $text ?: 'untitled';
}

/**
 * Генерация реалистичного контента статьи.
 */
function generateArticleContent(Faker $faker): string
{
    $paragraphs = $faker->numberBetween(5, 10);
    $content = '';
    
    for ($i = 0; $i < $paragraphs; $i++) {
        $content .= '<p>' . $faker->paragraphs(3, true) . '</p>';
        
        // Иногда добавляем подзаголовок
        if ($faker->boolean(30)) {
            $content .= '<h2>' . $faker->sentence(4) . '</h2>';
        }
    }
    
    return $content;
}