<?php

namespace App\Livewire;

use App\Models\ChurchRole;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PeopleTable extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $gender = '';

    #[Url]
    public string $maritalStatus = '';

    #[Url]
    public string $ministry = '';

    #[Url]
    public string $role = '';

    #[Url]
    public string $tag = '';

    #[Url]
    public string $shepherd = '';

    #[Url]
    public string $hasTelegram = '';

    #[Url]
    public string $hasUser = '';

    #[Url]
    public int $perPage = 25;

    public array $selectedIds = [];

    public bool $selectAll = false;

    protected int $churchId;

    public function mount(): void
    {
        $this->churchId = $this->getChurchId();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedGender(): void
    {
        $this->resetPage();
    }

    public function updatedMaritalStatus(): void
    {
        $this->resetPage();
    }

    public function updatedMinistry(): void
    {
        $this->resetPage();
    }

    public function updatedRole(): void
    {
        $this->resetPage();
    }

    public function updatedTag(): void
    {
        $this->resetPage();
    }

    public function updatedShepherd(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedIds = $this->getFilteredQuery()->pluck('id')->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'gender', 'maritalStatus', 'ministry', 'role', 'tag', 'shepherd', 'hasTelegram', 'hasUser']);
        $this->resetPage();
    }

    public function clearSelection(): void
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    public function togglePerson(int $personId): void
    {
        if (in_array($personId, $this->selectedIds)) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, [$personId]));
        } else {
            $this->selectedIds[] = $personId;
        }
    }

    public function render(): View
    {
        $churchId = $this->getChurchId();

        $people = $this->getFilteredQuery()->paginate($this->perPage);

        $tags = Tag::where('church_id', $churchId)->orderBy('name')->get();
        $ministries = Ministry::where('church_id', $churchId)->orderBy('name')->get();
        $churchRoles = ChurchRole::where('church_id', $churchId)->orderBy('sort_order')->get();
        $shepherds = Person::where('church_id', $churchId)
            ->where('is_shepherd', true)
            ->orderBy('last_name')
            ->get();

        $activeFiltersCount = collect([
            $this->gender, $this->maritalStatus, $this->ministry,
            $this->role, $this->tag, $this->shepherd,
            $this->hasTelegram, $this->hasUser,
        ])->filter()->count();

        return view('livewire.people-table', compact(
            'people', 'tags', 'ministries', 'churchRoles', 'shepherds', 'activeFiltersCount'
        ));
    }

    protected function getFilteredQuery()
    {
        $churchId = $this->getChurchId();

        $query = Person::where('church_id', $churchId)
            ->notGuest()
            ->with(['tags', 'ministries', 'churchRoleRelation', 'shepherd']);

        if ($this->search) {
            $useScout = config('scout.driver') === 'meilisearch';
            if ($useScout) {
                $ids = Person::search($this->search)
                    ->where('church_id', $churchId)
                    ->keys();
                $query->whereIn('id', $ids);
            } else {
                $query->search($this->search);
            }
        }

        if ($this->gender) {
            $query->where('gender', $this->gender);
        }

        if ($this->maritalStatus) {
            $query->where('marital_status', $this->maritalStatus);
        }

        if ($this->ministry) {
            $query->whereHas('ministries', fn ($q) => $q->where('ministries.id', $this->ministry));
        }

        if ($this->role) {
            $query->where('church_role_id', $this->role);
        }

        if ($this->tag) {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $this->tag));
        }

        if ($this->shepherd) {
            if ($this->shepherd === 'none') {
                $query->whereNull('shepherd_id');
            } else {
                $query->where('shepherd_id', $this->shepherd);
            }
        }

        if ($this->hasTelegram === 'yes') {
            $query->whereNotNull('telegram_chat_id');
        } elseif ($this->hasTelegram === 'no') {
            $query->whereNull('telegram_chat_id');
        }

        if ($this->hasUser === 'yes') {
            $query->whereNotNull('user_id');
        } elseif ($this->hasUser === 'no') {
            $query->whereNull('user_id');
        }

        return $query->orderBy('first_name')->orderBy('last_name');
    }

    protected function getChurchId(): int
    {
        $user = auth()->user();

        if ($user->isSuperAdmin() && session()->has('impersonate_church_id')) {
            return (int) session('impersonate_church_id');
        }

        return (int) $user->church_id;
    }
}
