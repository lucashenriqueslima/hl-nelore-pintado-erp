<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DealerResource\Pages;
use App\Filament\Resources\DealerResource\RelationManagers;
use App\Models\Dealer;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DealerResource extends Resource
{
    protected static ?string $model = Dealer::class;

    protected static ?string $modelLabel = 'Negociador';
    protected static ?string $pluralModelLabel = 'Negociadores';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informações Pessoais')
                    ->columns(2)
                    ->schema(
                        [
                            Forms\Components\TextInput::make('name')
                                ->label('Nome')
                                ->required()
                                ->columnSpanFull()
                                ->maxLength(255),
                            Forms\Components\Select::make('document_type')
                                ->label('Cadastrar por CNPJ ou CPF')
                                ->options([
                                    'cnpj' => 'CNPJ',
                                    'cpf' => 'CPF',
                                ])
                                ->native(false)
                                ->live()
                                ->dehydrated(false)
                                ->required(),
                            Forms\Components\TextInput::make('document')
                                ->label(fn(Get $get) => $get('document_type') === 'cpf' ? 'CPF' : 'CNPJ')
                                ->mask(fn(Get $get) => $get('document_type') === 'cpf' ? '999.999.999-99' : '99.999.999/9999-99')
                                ->rule(fn(Get $get) => $get('document_type') === 'cpf' ? 'cpf' : 'cnpj')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            Forms\Components\TextInput::make('phone')
                                ->label('Telefone')
                                ->required()
                                ->maxLength(15)
                                ->mask('(99) 99999-9999'),
                            Forms\Components\TextInput::make('email')
                                ->label('E-mail')
                                ->email()
                                ->required()
                                ->maxLength(255),
                        ]
                    ),
                Section::make('Informações de Endereço')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('cep')
                            ->label('CEP')
                            ->required()
                            ->maxLength(9)
                            ->mask('99999-999')
                            ->reactive()
                            ->afterStateUpdated(fn($state, callable $set) => static::buscarEnderecoPorCep($state, $set)),
                        Forms\Components\TextInput::make('address')
                            ->label('Endereço')
                            ->required(),
                        Forms\Components\TextInput::make('number')
                            ->label('Número')
                            ->required(),
                        Forms\Components\TextInput::make('neighborhood')
                            ->label('Bairro')
                            ->required(),
                        Forms\Components\TextInput::make('city')
                            ->label('Cidade')
                            ->required(),
                        Forms\Components\TextInput::make('state')
                            ->label('Estado')
                            ->required(),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('document')
                    ->label('CPF/CNPJ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Endereço')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDealers::route('/'),
            'create' => Pages\CreateDealer::route('/create'),
            'edit' => Pages\EditDealer::route('/{record}/edit'),
        ];
    }

    public static function buscarEnderecoPorCep($cep, callable $set)
    {

        if (!$cep) {
            return;
        }
        // Remove caracteres indesejados do CEP
        $cep = preg_replace('/[^0-9]/', '', $cep);

        if (strlen($cep) === 8) { // Verifica se o CEP é válido
            $response = file_get_contents("https://viacep.com.br/ws/{$cep}/json/");

            if ($response) {
                $data = json_decode($response, true);

                if (!isset($data['erro'])) {
                    // Preenche os campos com os dados do endereço retornado
                    $set('address', $data['logradouro'] ?? '');
                    $set('neighborhood', $data['bairro'] ?? '');
                    $set('city', $data['localidade'] ?? '');
                    $set('state', $data['uf'] ?? '');
                }
            }
        }
    }
}
