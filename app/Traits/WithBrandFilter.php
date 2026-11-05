<?php

namespace App\Traits;

use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

trait WithBrandFilter
{
    #[Locked]
    public ?int $brand = null;


    // Livewire ejecuta mount{NombreDelTrait} automáticamente
public function mountWithBrandFilter(): void
{
    $user = auth()->user();

    if (!$user) {
        $this->brand = null;
        return;
    }

    $this->brand = $user->hasRole('SuperAdmin')
        ? session('brand_id')
        : $user->brand_id;
}
    public function onBrandChanged(?int $brandId): void
    {
        $this->brand = $brandId;
    }
}
