@extends('layouts.app')

@section('title', 'Semua Notifikasi')

@section('content')
<div class="max-w-4xl mx-auto mt-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-200">
            <i class="fas fa-bell text-rose-500 mr-2"></i> {{ __('Semua Notifikasi') }}
        </h2>
        @if(auth()->user()->unreadNotifications->count() > 0)
        <form action="{{ route('notifications.markAllAsRead') }}" method="POST">
            @csrf
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-sm shadow-sm transition">
                <i class="fas fa-check-double mr-2"></i> {{ __('Tandai Semua Dibaca') }}
            </button>
        </form>
        @endif
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-white/5 overflow-hidden">
        <ul class="divide-y divide-slate-100 dark:divide-white/5">
            @forelse($notifications as $notification)
                <li class="p-4 sm:p-5 hover:bg-slate-50 dark:hover:bg-white/5 transition flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 {{ $notification->read_at ? 'opacity-75' : 'bg-blue-50/30 dark:bg-blue-900/10' }}">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if(empty($notification->read_at))
                                <span class="flex h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                            @endif
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500"><i class="far fa-clock mr-1"></i> {{ $notification->created_at->format('d M Y, H:i') }} ({{ $notification->created_at->diffForHumans() }})</span>
                        </div>
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-200 leading-relaxed">
                            {{ $notification->data['message'] ?? __('Belum ada notifikasi baru.') }}
                        </p>
                    </div>
                    
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        @if(isset($notification->data['url']))
                            <a href="{{ $notification->data['url'] }}" class="text-sm text-forest hover:text-blue-700 font-semibold border border-slate-200 dark:border-white/10 px-3 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-white/5 transition">
                                <i class="fas fa-external-link-alt mr-1"></i> {{ __('Lihat Data') }}
                            </a>
                        @endif

                        @if(empty($notification->read_at))
                            <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-emerald-600 hover:text-emerald-700 bg-emerald-50 dark:bg-emerald-900/20 px-3 py-1.5 rounded-lg font-semibold transition" title="{{ __('Tandai Dibaca') }}">
                                    <i class="fas fa-check mr-1"></i> {{ __('Selesai') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </li>
            @empty
                <li class="p-12 text-center text-slate-500">
                    <i class="fas fa-check-circle text-4xl mb-3 text-slate-300 dark:text-slate-600"></i>
                    <p class="text-sm">{{ __('Belum ada notifikasi baru.') }}</p>
                </li>
            @endforelse
        </ul>
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
