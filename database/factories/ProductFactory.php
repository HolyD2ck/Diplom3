<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\Photo;
use App\Models\Supplier;
use Illuminate\Support\Facades\File;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $categoryId = Category::inRandomOrder()->value('id');
        $supplierId = Supplier::inRandomOrder()->value('id');
        return [
            'категория_id' => $categoryId,
            'поставщик_id' => $supplierId,
            'название' => $this->ГенераторИмен($categoryId),
            'описание' => $this->faker->text(),
            'производитель' => $this->ГенераторПроизводителей($categoryId),
            'цена' => $this->faker->randomFloat(2, 10000, 100000),
            'скидка' => $this->faker->numberBetween(0, 90),
            'дата_выпуска' => $this->faker->dateTimeBetween('-10 years', '-1 year')->format('Y-m-d'),
            'дата_поступления_в_продажу' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];
    }
    public function templates()
    {
        return [
            'Видеокарты' => ['GeForce RTX', 'Radeon RX', 'GTX', 'Quadro'],
            'Процессоры' => ['Intel Core i7', 'AMD Ryzen', 'Xeon', 'Pentium'],
            'Материнские платы' => ['ASUS ROG', 'MSI Gaming', 'Gigabyte Ultra', 'ASRock Phantom'],
            'Оперативная память' => ['Kingston HyperX', 'Corsair Vengeance', 'G.Skill Ripjaws', 'Crucial Ballistix'],
            'Корпуса' => ['Cooler Master', 'NZXT H510', 'Thermaltake View', 'Fractal Design'],
            'Блоки питания' => ['Corsair RM', 'Seasonic Focus', 'Cooler Master V', 'EVGA SuperNova'],
            'SSD' => ['Samsung Evo', 'Crucial MX', 'WD Blue', 'Kingston A'],
            'HDD' => ['Seagate Barracuda', 'WD Black', 'Toshiba X300', 'Hitachi Ultrastar'],
            'Мониторы' => ['Dell Ultrasharp', 'ASUS TUF Gaming', 'LG UltraGear', 'Samsung Odyssey'],
        ];
    }
    public function ГенераторИмен($categoryId)
    {
        $categoryName = Category::where('id', $categoryId)->value('название');

        $nameTemplates = $this->templates()[$categoryName];
        $randomSuffix = rand(2000, 9999);
        $randomName = $nameTemplates[array_rand($nameTemplates)] . ' ' . $randomSuffix;

        return $randomName;
    }
    public function ГенераторПроизводителей($categoryId)
    {
        $categoryName = Category::where('id', $categoryId)->value('название');

        $nameTemplates = $this->templates()[$categoryName];
        $randomName = $nameTemplates[array_rand($nameTemplates)];

        return $randomName;
    }
    protected static function newFactory()
    {
        return ProductFactory::new()
            ->afterCreating(function (Product $product) {
                static::createPhotos($product);
            });
    }

    private static function createPhotos(Product $product)
    {
        $mainPhotosPath = public_path('faker/main/' . $product->категория->название);
        $otherPhotosPath = public_path('faker/other/' . $product->категория->название);

        $mainPhotos = array_diff(scandir($mainPhotosPath), ['.', '..']);
        $otherPhotos = array_diff(scandir($otherPhotosPath), ['.', '..']);

        $mainPhotoFile = $mainPhotos[array_rand($mainPhotos)];

        $additionalPhotosFiles = count($otherPhotos) > 3 ? array_rand(array_flip($otherPhotos), 3) : $otherPhotos;

        $destinationPath = storage_path('app/public/photos/products/' . $product->название);
        File::makeDirectory($destinationPath, 0755, true);

        $mainPhotoSource = $mainPhotosPath . '/' . $mainPhotoFile;
        $mainPhotoDestination = $destinationPath . '/' . $mainPhotoFile;
        File::copy($mainPhotoSource, $mainPhotoDestination);

        Photo::create([
            'путь' => 'photos/products/' . $product->название . '/' . $mainPhotoFile,
            'основное' => true,
            'товар_id' => $product->id,
        ]);

        foreach ($additionalPhotosFiles as $photoFile) {
            $photoSource = $otherPhotosPath . '/' . $photoFile;
            $photoDestination = $destinationPath . '/' . $photoFile;
            File::copy($photoSource, $photoDestination);

            Photo::create([
                'путь' => 'photos/products/' . $product->название . '/' . $photoFile,
                'основное' => false,
                'товар_id' => $product->id,
            ]);
        }
    }

}
