<?php

use Livewire\Volt\Component;

new class extends Component {
    //
    public $user;
    public $notifications = [];

    public function mount()
    {
        $this->user = auth()->user();
        $this->notifications = $this->user->notifications()->get();
    }

    public function loadNotifications()
    {
        $this->notifications = auth()->user()->notifications()->latest()->get();
    }
    
    public function markAsRead($notificationId)
    {
        $notification = auth()->user()->notifications()->find($notificationId);
        if ($notification && is_null($notification->read_at)) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

}; ?>

<div>
    <div class="dropdown notification-wrapper">
        <button class="dropdown-toggle bg-transparent border-0 position-relative" type="button" id="notifications"
            data-bs-toggle="dropdown" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" class="bi bi-bell-fill"
                viewBox="0 0 16 16">
                <path
                    d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2m.995-14.901a1 1 0 1 0-1.99 0A5 5 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901" />
            </svg>
            <span class="notification-badge">{{ $user->unreadNotifications->count() }}</span>
        </button>

        <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="notifications" style="width: 320px; max-height: 400px; overflow-y: auto;">
            @if($notifications->isEmpty())
                <li class="dropdown-item text-center text-muted">No tienes notificaciones nuevas</li>
            @else
                @foreach($notifications as $notification)
                    <li class="dropdown-item {{ is_null($notification->read_at) ? 'bg-light fw-bold' : 'text-muted' }}">
                        <a href="#" class="d-block text-decoration-none text-dark" wire:click.prevent="markAsRead('{{ $notification->id }}')">
                            <div style="white-space: normal; word-break: break-word;">
                                {{ $notification->data['message'] ?? 'Sin mensaje' }}
                            </div>
                            <small class="text-muted d-block mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                                @if(is_null($notification->read_at))
                                    · <span class="badge bg-primary">Nuevo</span>
                                @endif
                            </small>
                        </a>
                    </li>
                @endforeach
            @endif
        </ul>
    </div>
</div>
