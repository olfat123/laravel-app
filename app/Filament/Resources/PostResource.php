<?php

namespace App\Filament\Resources;

use App\Enums\RolesEnum;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Str;
use App\Filament\Resources\PostResource\Pages;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Filament::auth()->user()?->hasRole(RolesEnum::Admin) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Translations')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('English')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Title (English)')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                        if ($operation === 'create') {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),
                                Textarea::make('excerpt')
                                    ->label('Excerpt (English)')
                                    ->rows(3)
                                    ->maxLength(500),
                                Forms\Components\RichEditor::make('content')
                                    ->label('Content (English)')
                                    ->required()
                                    ->columnSpanFull()
                                    ->fileAttachmentsDisk('public'),
                            ]),
                        Forms\Components\Tabs\Tab::make('Arabic (عربي)')
                            ->schema([
                                TextInput::make('title_ar')
                                    ->label('العنوان بالعربي')
                                    ->extraInputAttributes(['dir' => 'rtl', 'lang' => 'ar'])
                                    ->maxLength(255),
                                Textarea::make('excerpt_ar')
                                    ->label('المقتطف بالعربي')
                                    ->extraInputAttributes(['dir' => 'rtl', 'lang' => 'ar'])
                                    ->rows(3)
                                    ->maxLength(500),
                                Forms\Components\RichEditor::make('content_ar')
                                    ->label('المحتوى بالعربي')
                                    ->columnSpanFull()
                                    ->extraInputAttributes(['dir' => 'rtl', 'lang' => 'ar'])
                                    ->fileAttachmentsDisk('public'),
                            ]),
                    ]),

                Section::make('Settings')
                    ->columns(2)
                    ->schema([
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(Post::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('post_category_id')
                            ->label('Category')
                            ->options(PostCategory::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->createOptionForm([
                                TextInput::make('name')->required()->maxLength(255),
                                TextInput::make('name_ar')
                                    ->label('Name (Arabic)')
                                    ->extraInputAttributes(['dir' => 'rtl', 'lang' => 'ar'])
                                    ->maxLength(255),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(PostCategory::class, 'slug')
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return PostCategory::create($data)->id;
                            }),
                        Select::make('author_id')
                            ->label('Author')
                            ->options(User::query()->pluck('name', 'id'))
                            ->default(fn () => Filament::auth()->user()?->id)
                            ->required()
                            ->searchable(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft'     => 'Draft',
                                'published' => 'Published',
                            ])
                            ->default('draft')
                            ->required(),
                        DateTimePicker::make('published_at')
                            ->label('Publish At')
                            ->default(now())
                            ->nullable(),
                    ]),

                Section::make('Cover Image')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('cover')
                            ->label('Cover Image')
                            ->collection('cover')
                            ->image()
                            ->imageEditor()
                            ->maxSize(5120),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('cover')
                    ->collection('cover')
                    ->conversion('thumb')
                    ->label('Cover')
                    ->width(80)
                    ->height(50),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('author.name')
                    ->label('Author')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft'     => 'warning',
                        default     => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Published')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'     => 'Draft',
                        'published' => 'Published',
                    ]),
                Tables\Filters\SelectFilter::make('post_category_id')
                    ->label('Category')
                    ->options(PostCategory::orderBy('name')->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit'   => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
