<div x-data="{ open: false }">
    <!-- Checkbox Input -->
    <input type="checkbox" x-on:click="open = true" x-bind:checked="isChecked">

    <!-- Confirmation Dialog -->
    <div x-show="open" x-on:close.stop="open = false">
        Are you sure?
        <button x-on:click="open = false; $wire.toggleCheckbox()">Yes</button>
        <button x-on:click="open = false">No</button>
    </div>
</div>