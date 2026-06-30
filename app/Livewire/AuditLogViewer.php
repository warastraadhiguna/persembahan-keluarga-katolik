<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogViewer extends Component
{
    use WithPagination;

    public string $filterUserId = '';
    public string $filterAction = '';
    public string $filterDateFrom = '';
    public string $filterDateTo = '';

    public ?int $expandedId = null;

    public function updatingFilterUserId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterAction(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function logs()
    {
        return AuditLog::query()
            ->with('user:id,name')
            ->when($this->filterUserId, fn ($q) => $q->where('user_id', $this->filterUserId))
            ->when($this->filterAction, fn ($q) => $q->where('action', $this->filterAction))
            ->when($this->filterDateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->filterDateTo))
            ->orderByDesc('created_at')
            ->paginate(25);
    }

    #[Computed]
    public function userOptions(): Collection
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    public function toggleDetail(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function resetFilters(): void
    {
        $this->filterUserId = '';
        $this->filterAction = '';
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.audit-log-viewer');
    }
}
