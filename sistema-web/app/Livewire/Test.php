<?php

namespace App\Livewire;

use Livewire\Component;

class Test extends Component
{
    public $count = 0;

    public $texto = 'Hola Mundo';
    public function render()
    {
        return view('livewire.test');
    }

    public function increment()
    {

        $this->count++;
    }
}
