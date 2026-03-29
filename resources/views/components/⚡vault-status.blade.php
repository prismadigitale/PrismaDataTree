<?php

use Livewire\Component;
use App\Services\VaultService;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;

new class extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    public bool $isUnlocked = false;

    public function mount()
    {
        $this->checkStatus();
    }

    public function checkStatus()
    {
        $this->isUnlocked = app(VaultService::class)->isUnlocked();
    }

    public function unlockAction(): Action
    {
        return Action::make('unlock')
            ->label('Sblocca Vault')
            ->icon('heroicon-o-lock-closed')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Sblocca Vault Sicuro')
            ->modalDescription('Inserisci la tua Master Passphrase per decriptare i campi protetti dal vault.')
            ->modalSubmitActionLabel('Sblocca')
            ->form([
                TextInput::make('passphrase')
                    ->password()
                    ->required()
                    ->autofocus(),
            ])
            ->action(function (array $data) {
                $service = app(VaultService::class);
                $success = $service->unlock(auth()->user(), $data['passphrase']);

                if ($success) {
                    Notification::make()
                        ->title('Vault Sbloccato')
                        ->success()
                        ->send();
                    
                    $this->checkStatus();
                    $this->dispatch('vault-unlocked'); // Inform other components to refresh
                } else {
                    Notification::make()
                        ->title('Passphrase non valida')
                        ->danger()
                        ->send();
                }
            });
    }

    public function lockAction(): Action
    {
        return Action::make('lock')
            ->label('Blocca Vault')
            ->icon('heroicon-o-lock-open')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Blocca Vault Sicuro')
            ->modalDescription('Sei sicuro di voler bloccare il vault? I campi protetti saranno nascosti.')
            ->modalSubmitActionLabel('Blocca Database')
            ->action(function () {
                app(VaultService::class)->lock();
                
                Notification::make()
                    ->title('Vault Bloccato')
                    ->success()
                    ->send();
                
                $this->checkStatus();
                $this->dispatch('vault-locked');
            });
    }

    // Used if user has no vault setup yet
    public function setupAction(): Action
    {
        return Action::make('setup')
            ->label('Configura Vault')
            ->icon('heroicon-o-shield-check')
            ->color('gray')
            ->modalHeading('Configura Vault Sicuro')
            ->modalDescription('Crea una Master Passphrase per crittografare i tuoi dati più sensibili. Questa passphrase non viene mai salvata e non può essere recuperata in caso di smarrimento.')
            ->form([
                TextInput::make('passphrase')
                    ->password()
                    ->label('Master Passphrase')
                    ->required()
                    ->minLength(8),
                TextInput::make('passphrase_confirmation')
                    ->password()
                    ->label('Conferma Passphrase')
                    ->required()
                    ->same('passphrase'),
            ])
            ->action(function (array $data) {
                app(VaultService::class)->setupVault(auth()->user(), $data['passphrase']);
                
                Notification::make()
                    ->title('Vault Creato e Sbloccato')
                    ->success()
                    ->send();
                
                $this->checkStatus();
                $this->dispatch('vault-unlocked');
            });
    }
    public function changePassphraseAction(): Action
    {
        return Action::make('changePassphrase')
            ->label('Cambia Passphrase')
            ->icon('heroicon-o-key')
            ->color('gray')
            ->modalHeading('Cambia Master Passphrase')
            ->modalDescription('Inserisci la tua attuale Master Passphrase e scegline una nuova. Questo modificherà la chiave di accesso senza alterare i tuoi dati.')
            ->form([
                TextInput::make('current_passphrase')
                    ->password()
                    ->label('Passphrase Attuale')
                    ->required()
                    ->autofocus(),
                TextInput::make('new_passphrase')
                    ->password()
                    ->label('Nuova Passphrase')
                    ->required()
                    ->minLength(8),
                TextInput::make('passphrase_confirmation')
                    ->password()
                    ->label('Conferma Nuova Passphrase')
                    ->required()
                    ->same('new_passphrase'),
            ])
            ->action(function (array $data) {
                $service = app(VaultService::class);
                $success = $service->changePassphrase(auth()->user(), $data['current_passphrase'], $data['new_passphrase']);
                
                if ($success) {
                    Notification::make()
                        ->title('Passphrase Aggiornata con Successo')
                        ->success()
                        ->send();
                        
                    $this->checkStatus();
                    $this->dispatch('vault-unlocked');
                } else {
                    Notification::make()
                        ->title('Passphrase Attuale Errata')
                        ->danger()
                        ->send();
                }
            });
    }
};
?>

<div class="flex items-center gap-2" wire:poll.30s="checkStatus">
    @if(auth()->user()->vault === null)
        {{ ($this->setupAction)(['color' => 'gray', 'size' => 'sm']) }}
    @else
        {{ ($this->changePassphraseAction)(['iconButton' => true, 'color' => 'gray', 'size' => 'sm']) }}
        @if($isUnlocked)
            {{ ($this->lockAction)(['color' => 'success', 'size' => 'sm']) }}
        @else
            {{ ($this->unlockAction)(['color' => 'danger', 'size' => 'sm']) }}
        @endif
    @endif

    <x-filament-actions::modals />
</div>