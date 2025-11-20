<div>
    <div style="text-align: center">

        <button wire:click="increment">+</button>

        <h1>{{ $count }}</h1>

        <p>{{ $texto }}</p>
        <input type="text" wire:model.live="texto" placeholder="">

    </div>
</div>
