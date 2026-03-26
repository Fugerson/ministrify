import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

// Only start Alpine if Livewire hasn't already started it
// Livewire 3 auto-initializes Alpine — starting it twice causes errors
if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}
