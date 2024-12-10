<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Filament\Forms\Components\{Select, TextInput, Textarea, DatePicker, Repeater, FileUpload};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Symfony\Component\Translation\Loader\FileLoader;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('категория_id')
                    ->label('Категория')
                    ->options(Category::all()->pluck('название', 'id'))
                    ->required()
                    ->searchable()
                    ->reactive(),
                Repeater::make('атрибуты')
                    ->label('Атрибуты')
                    ->maxItems(1)
                    ->schema(function ($get) {
                        $categoryId = $get('категория_id');
                        $attributes = Category::find($categoryId)?->аттрибуты;

                        return $attributes?->map(function ($attribute) {
                            return TextInput::make($attribute->id)
                                ->label($attribute->название)
                                ->required();
                        })->toArray() ?? [];
                    })
                    ->columns(3),
                TextInput::make('название')->maxLength(100),
                Textarea::make('описание')->columnSpanFull(),
                TextInput::make('производитель')->maxLength(100),
                TextInput::make('цена')->numeric()->default(0)->inputMode('decimal')->required()->minValue(0),
                TextInput::make('скидка')->numeric()->default(0)->inputMode('numeric')->minValue(0)->maxValue(100),
                DatePicker::make('дата_выпуска'),
                DatePicker::make('дата_поступления_в_продажу'),
                FileUpload::make('основное_фото')->image()->required()->disk('public')->label('Основное фото')
                    ->directory(function ($get) {
                        $name = $get('название');
                        return 'photos/products/' . $name;
                    }),
                FileUpload::make('фотографии')
                    ->image()
                    ->required()
                    ->multiple()
                    ->disk('public')
                    ->label('Вторичные фотографии')
                    ->directory(function ($get) {
                        $name = $get('название');
                        return 'photos/products/' . $name;
                    })
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('категория.название')
                    ->numeric()
                    ->label('категория')
                    ->sortable(),

                Tables\Columns\TextColumn::make('название')
                    ->searchable(),

                Tables\Columns\TextColumn::make('производитель')
                    ->searchable(),

                Tables\Columns\TextColumn::make('цена')
                    ->numeric()
                    ->default(0)
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('скидка')
                    ->numeric()
                    ->sortable()
                    ->default(0)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('отзывы.отзыв')
                    ->searchable()
                    ->toggleable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('средняяОценка.рейтинг')
                    ->label('Средняя оценка')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\ImageColumn::make('основноеФото.путь')
                    ->label('Фото')
                    ->size(80)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('дата_выпуска')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('дата_поступления_в_продажу')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getRelations(): array
    {
        return [];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}