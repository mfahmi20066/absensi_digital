

import Alpine from 'alpinejs';
import './alert';
import { Html5Qrcode } from 'html5-qrcode';
import html2canvas from 'html2canvas';
import { initTheme, setupThemeToggles } from './theme';

initTheme();
setupThemeToggles();

window.Html5Qrcode = Html5Qrcode;
window.html2canvas = html2canvas;

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('passwordStrength', () => ({
        pass: '',
        get score() {
            let s = 0;
            if (this.pass.length >= 8) s++;
            if (this.pass.length >= 12) s++;
            if (/[A-Z]/.test(this.pass) && /[a-z]/.test(this.pass)) s++;
            if (/\d/.test(this.pass) && /[^A-Za-z0-9]/.test(this.pass)) s++;
            return s;
        },
        get scoreLabel() {
            if (!this.pass) return '';
            const labels = ['Sangat lemah', 'Lemah', 'Cukup', 'Kuat', 'Sangat kuat'];
            return labels[this.score];
        },
    }));

    Alpine.data('otpBoxes', () => ({
        boxes: ['', '', '', '', '', ''],
        loading: false,
        countdown: 30,
        timer: null,

        init() {
            this.$nextTick(() => this.focusBox(0));

            this.$watch('filled', (val) => {
                if (val) {
                    this.loading = true;
                    setTimeout(() => this.$refs.form.submit(), 250);
                }
            });

            this.timer = setInterval(() => {
                if (this.countdown > 0) this.countdown--;
            }, 1000);
        },

        get filled() {
            return this.boxes.join('').length === 6;
        },

        focusBox(i) {
            if (i < 0 || i > 5) return;
            const inputs = this.$refs.boxes.querySelectorAll('input');
            inputs[i]?.focus();
            inputs[i]?.select?.();
        },

        onInput(i, e) {
            const digit = e.target.value.replace(/\D/g, '').slice(-1);
            this.boxes[i] = digit;
            if (digit && i < 5) this.focusBox(i + 1);
        },

        onBackspace(i) {
            if (this.boxes[i] === '' && i > 0) this.focusBox(i - 1);
        },

        onPaste(e) {
            const raw = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
            this.boxes = ['', '', '', '', '', ''];
            raw.split('').forEach((d, idx) => { this.boxes[idx] = d; });
            this.focusBox(Math.min(raw.length, 5));
        },
    }));

    Alpine.data('resetPassword', () => ({
        submitting: false,
        showPass: false,
        showConfirm: false,
        pass: '',
        pass2: '',
        countdown: 30,
        timer: null,
        init() {
            this.timer = setInterval(() => {
                if (this.countdown > 0) this.countdown--;
            }, 1000);
        },
    }));
});

Alpine.start();
