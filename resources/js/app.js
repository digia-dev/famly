import Chart from 'chart.js/auto';
window.Chart = Chart; // Membuat Chart tersedia secara global

import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);
window.Alpine = Alpine;

Alpine.start();

