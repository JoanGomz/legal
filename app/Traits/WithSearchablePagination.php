<?php
// App\Traits\WithSearchablePagination.php
namespace App\Traits;

use Livewire\WithPagination;

trait WithSearchablePagination
{
    use WithPagination;
    
    public $search = '';
    protected $paginationTheme = 'tailwind';
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    protected function paginateData($collection, $searchFields = [], $perPage = 10)
    {
        if (!empty($this->search)) {
            $searchTerm = strtolower($this->search);
            $collection = $collection->filter(function ($item) use ($searchFields, $searchTerm) {
                foreach ($searchFields as $field) {
                    if (str_contains(strtolower($item[$field] ?? ''), $searchTerm)) {
                        return true;
                    }
                }
                return false;
            });
        }
        
        return $collection->paginate($perPage);
    }
}