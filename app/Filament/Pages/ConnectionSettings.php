<?php

namespace App\Filament\Pages;

use App\Models\CommunicationConnection;
use App\Models\Company;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ConnectionSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static string|UnitEnum|null $navigationGroup = 'Configuracion';

    protected static ?string $navigationLabel = 'Conexiones';

    protected static ?string $title = 'Conexiones';

    protected static ?string $slug = 'conexiones';

    protected static ?int $navigationSort = 1;

    /**
     * Estado completo del formulario de configuracion por pestanas.
     *
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->data['company_id'] ??= Company::query()->orderBy('name')->value('id');

        $this->fillConnectionData();
    }

    /**
     * Recarga los datos cuando cambia la empresa seleccionada.
     */
    public function updatedDataCompanyId(): void
    {
        $this->fillConnectionData();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Empresa')
                    ->schema([
                        Select::make('company_id')
                            ->label('Empresa')
                            ->options(Company::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->helperText('Cada empresa tiene una configuracion por canal.'),
                    ]),
                Tabs::make('Canales')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Telegram')
                            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                            ->schema($this->telegramSchema()),
                        Tab::make('Email SMTP')
                            ->icon(Heroicon::OutlinedEnvelope)
                            ->schema($this->emailSchema()),
                        Tab::make('WhatsApp Evolution API')
                            ->icon(Heroicon::OutlinedQrCode)
                            ->schema($this->whatsappSchema()),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('connections-form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Guardar conexiones')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ]);
    }

    /**
     * Guarda las tres pestanas como una conexion unica por canal y empresa.
     */
    public function save(): void
    {
        $state = $this->form->getState();
        $companyId = $state['company_id'];

        $this->saveChannel($companyId, 'telegram', $state['telegram'] ?? []);
        $this->saveChannel($companyId, 'email', $state['email'] ?? []);
        $this->saveChannel($companyId, 'whatsapp', $state['whatsapp'] ?? []);

        Notification::make()
            ->success()
            ->title('Conexiones guardadas')
            ->body('La configuracion de Telegram, Email y WhatsApp fue actualizada.')
            ->send();

        $this->fillConnectionData();
    }

    /**
     * Campos necesarios para configurar Telegram Bot API.
     *
     * @return array<int, mixed>
     */
    private function telegramSchema(): array
    {
        return [
            Toggle::make('telegram.is_enabled')
                ->label('Habilitar Telegram')
                ->default(false),
            TextInput::make('telegram.name')
                ->label('Nombre interno')
                ->default('Telegram')
                ->required()
                ->maxLength(255),
            TextInput::make('telegram.username')
                ->label('Usuario del bot')
                ->placeholder('@mi_bot')
                ->maxLength(255),
            TextInput::make('telegram.secret')
                ->label('Bot token')
                ->password()
                ->revealable(),
            TextInput::make('telegram.default_chat_id')
                ->label('Chat ID por defecto')
                ->helperText('Opcional. Para clientes se recomienda guardar el ID individual en el modulo Clientes.'),
            TextInput::make('telegram.webhook_url')
                ->label('Webhook Telegram')
                ->url()
                ->maxLength(255)
                ->columnSpanFull(),
        ];
    }

    /**
     * Campos necesarios para configurar correo SMTP.
     *
     * @return array<int, mixed>
     */
    private function emailSchema(): array
    {
        return [
            Toggle::make('email.is_enabled')
                ->label('Habilitar Email')
                ->default(false),
            TextInput::make('email.name')
                ->label('Nombre interno')
                ->default('Email SMTP')
                ->required()
                ->maxLength(255),
            TextInput::make('email.host')
                ->label('Host SMTP')
                ->placeholder('smtp.gmail.com')
                ->maxLength(255),
            TextInput::make('email.port')
                ->label('Puerto')
                ->numeric()
                ->placeholder('587'),
            Select::make('email.encryption')
                ->label('Cifrado')
                ->options([
                    'tls' => 'TLS',
                    'ssl' => 'SSL',
                    'none' => 'Sin cifrado',
                ])
                ->default('tls'),
            TextInput::make('email.username')
                ->label('Usuario SMTP')
                ->maxLength(255),
            TextInput::make('email.secret')
                ->label('Password SMTP')
                ->password()
                ->revealable(),
            TextInput::make('email.from_address')
                ->label('Correo remitente')
                ->email()
                ->maxLength(255),
            TextInput::make('email.from_name')
                ->label('Nombre remitente')
                ->maxLength(255),
        ];
    }

    /**
     * Campos necesarios para configurar WhatsApp con Evolution API.
     *
     * @return array<int, mixed>
     */
    private function whatsappSchema(): array
    {
        return [
            Toggle::make('whatsapp.is_enabled')
                ->label('Habilitar WhatsApp')
                ->default(false),
            TextInput::make('whatsapp.name')
                ->label('Nombre interno')
                ->default('WhatsApp Evolution API')
                ->required()
                ->maxLength(255),
            TextInput::make('whatsapp.base_url')
                ->label('URL base Evolution API')
                ->placeholder('https://evolution-api.midominio.com')
                ->url()
                ->maxLength(255),
            TextInput::make('whatsapp.instance_name')
                ->label('Nombre de instancia')
                ->maxLength(255),
            TextInput::make('whatsapp.secret')
                ->label('API key')
                ->password()
                ->revealable(),
            TextInput::make('whatsapp.webhook_url')
                ->label('Webhook')
                ->url()
                ->maxLength(255),
            TextInput::make('whatsapp.phone_number')
                ->label('Numero conectado')
                ->tel(),
            TextInput::make('whatsapp.connection_status')
                ->label('Estado de instancia')
                ->placeholder('open, close, connecting'),
            TextInput::make('whatsapp.pairing_code')
                ->label('Codigo de vinculacion')
                ->helperText('Opcional si Evolution API entrega pairing code.'),
            Textarea::make('whatsapp.qr_code')
                ->label('QR de conexion')
                ->rows(8)
                ->helperText('Espacio para pegar el QR/base64/texto retornado por Evolution API mientras conectamos el endpoint automatico.')
                ->columnSpanFull(),
        ];
    }

    /**
     * Llena el formulario con los registros existentes de la empresa.
     */
    private function fillConnectionData(): void
    {
        $companyId = $this->data['company_id'] ?? null;

        $this->form->fill([
            'company_id' => $companyId,
            'telegram' => $this->channelData($companyId, 'telegram', 'Telegram'),
            'email' => $this->channelData($companyId, 'email', 'Email SMTP'),
            'whatsapp' => $this->channelData($companyId, 'whatsapp', 'WhatsApp Evolution API'),
        ]);
    }

    /**
     * Convierte un registro de conexion en estado de formulario.
     *
     * @return array<string, mixed>
     */
    private function channelData(?int $companyId, string $channel, string $defaultName): array
    {
        $connection = $companyId
            ? CommunicationConnection::query()
                ->where('company_id', $companyId)
                ->where('channel', $channel)
                ->first()
            : null;

        $settings = $connection?->settings ?? [];

        return [
            'name' => $connection?->name ?? $defaultName,
            'is_enabled' => $connection?->is_enabled ?? false,
            'host' => $connection?->host,
            'port' => $connection?->port,
            'username' => $connection?->username,
            'secret' => $connection?->secret,
            'from_address' => $connection?->from_address,
            'from_name' => $connection?->from_name,
            'base_url' => $connection?->base_url,
            'instance_name' => $connection?->instance_name,
            'webhook_url' => $connection?->webhook_url,
            'default_chat_id' => $settings['default_chat_id'] ?? null,
            'encryption' => $settings['encryption'] ?? 'tls',
            'phone_number' => $settings['phone_number'] ?? null,
            'connection_status' => $settings['connection_status'] ?? null,
            'pairing_code' => $settings['pairing_code'] ?? null,
            'qr_code' => $settings['qr_code'] ?? null,
        ];
    }

    /**
     * Crea o actualiza una conexion para el canal indicado.
     *
     * @param  array<string, mixed>  $data
     */
    private function saveChannel(int $companyId, string $channel, array $data): void
    {
        $connection = CommunicationConnection::firstOrNew([
            'company_id' => $companyId,
            'channel' => $channel,
        ]);

        $connection->fill([
            'name' => $data['name'] ?? ucfirst($channel),
            'is_enabled' => (bool) ($data['is_enabled'] ?? false),
            'host' => $data['host'] ?? null,
            'port' => $data['port'] ?? null,
            'username' => $data['username'] ?? null,
            'secret' => $data['secret'] ?? null,
            'from_address' => $data['from_address'] ?? null,
            'from_name' => $data['from_name'] ?? null,
            'base_url' => $data['base_url'] ?? null,
            'instance_name' => $data['instance_name'] ?? null,
            'webhook_url' => $data['webhook_url'] ?? null,
            'settings' => $this->settingsForChannel($channel, $data),
        ]);

        $connection->save();
    }

    /**
     * Guarda los campos especificos de cada canal dentro de settings.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function settingsForChannel(string $channel, array $data): array
    {
        return match ($channel) {
            'telegram' => [
                'default_chat_id' => $data['default_chat_id'] ?? null,
            ],
            'email' => [
                'encryption' => $data['encryption'] ?? 'tls',
            ],
            'whatsapp' => [
                'phone_number' => $data['phone_number'] ?? null,
                'connection_status' => $data['connection_status'] ?? null,
                'pairing_code' => $data['pairing_code'] ?? null,
                'qr_code' => $data['qr_code'] ?? null,
            ],
            default => [],
        };
    }
}
