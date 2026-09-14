import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import * as bootstrap from 'bootstrap';
import 'chart.js/auto';
import { Chart } from 'chart.js';
import { initVoiceCapture } from './voice-capture';

window.bootstrap = bootstrap;
window.Chart = Chart;
window.initVoiceApp = initVoiceCapture;

window.Eltrack = {
    routes: {
        'voice.parse': '/voice/parse',
        'voice.store': '/voice/transactions',
        'voice.upload-audio': '/voice/upload-audio',
    },
};

document.addEventListener('DOMContentLoaded', () => {
    initMoneyInputs();
    initConfirmation();
    initToastAutoHide();
    initChartResizeFix();
    initVoiceCapture();
});

function initMoneyInputs() {
    document.querySelectorAll('input[data-format-money]').forEach((input) => {
        input.addEventListener('input', () => {
            let digits = input.value.replace(/\D/g, '');
            if (digits === '') {
                input.value = '';
                return;
            }
            input.value = new Intl.NumberFormat('id-ID').format(parseInt(digits, 10));
        });
        input.addEventListener('focus', () => {
            if (input.value === '0') {
                input.value = '';
            }
        });
    });
}

function initConfirmation() {
    document.addEventListener('click', (e) => {
        const el = e.target.closest('[data-confirm]');
        if (!el) {
            return;
        }
        if (!window.confirm(el.dataset.confirm)) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    });
}

function initToastAutoHide() {
    document.querySelectorAll('.toast').forEach((toast) => {
        if (window.bootstrap && bootstrap.Toast) {
            bootstrap.Toast.getOrCreateInstance(toast, { delay: 3500 }).show();
        }
    });
}

function initChartResizeFix() {
    const resizeObserver = new ResizeObserver(() => {
        setTimeout(() => window.Chart && window.Chart.instances && Object.values(window.Chart.instances).forEach((c) => c.resize()), 100);
    });
    document.querySelectorAll('.chart-wrap').forEach((wrap) => resizeObserver.observe(wrap));
}