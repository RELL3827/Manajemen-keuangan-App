export function initVoiceCapture() {
    document.querySelectorAll('[data-voice-capture]').forEach((root) => {
        if (root.dataset.vcReady) {
            return;
        }
        root.dataset.vcReady = '1';

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        const els = {
            start: root.querySelector('[data-vc-start]'),
            stop: root.querySelector('[data-vc-stop]'),
            retry: root.querySelectorAll('[data-vc-retry]'),
            cancel: root.querySelector('[data-vc-cancel]'),
            tip: root.querySelector('[data-vc-tip]'),
            status: root.querySelector('[data-vc-status]'),
            wave: root.querySelector('[data-vc-wave]'),
            live: root.querySelector('[data-vc-live]'),
            actions: root.querySelector('[data-vc-actions]'),
            err: root.querySelector('[data-vc-err]'),
            stage: root.querySelector('.vc-stage'),
            preview: root.querySelector('[data-vc-preview]'),
            quote: root.querySelector('[data-vc-quote]'),
            warnings: root.querySelector('[data-vc-warnings]'),
            saveErr: root.querySelector('[data-vc-save-err]'),
            form: root.querySelector('[data-vc-form]'),
            fieldType: root.querySelector('[data-vc-field-type]'),
            category: root.querySelector('[data-vc-category]'),
            account: root.querySelector('[data-vc-account]'),
            date: root.querySelector('[data-vc-date]'),
            description: root.querySelector('[data-vc-description]'),
            transcription: root.querySelector('[data-vc-transcription]'),
            duration: root.querySelector('[data-vc-duration]'),
            audioBox: root.querySelector('[data-vc-audio-box]'),
            audio: root.querySelector('[data-vc-audio]'),
            clearAudio: root.querySelector('[data-vc-clear-audio]'),
            save: root.querySelector('[data-vc-save]'),
        };

        let recognition = null;
        let recorder = null;
        let audioChunks = [];
        let audioBlob = null;
        let audioURL = null;
        let listening = false;
        let finalTranscript = '';
        let interimTranscript = '';
        let parsePayload = null;
        let csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        function reset() {
            stopRecorder();
            stopRecognition();
            finalTranscript = '';
            interimTranscript = '';
            audioBlob = null;
            parsePayload = null;
            els.live.classList.add('d-none');
            els.live.textContent = '';
            els.wave.classList.add('d-none');
            els.actions.classList.add('d-none');
            els.err.classList.add('d-none');
            els.stage.classList.remove('d-none');
            els.preview.classList.add('d-none');
            els.start.classList.remove('listening');
            els.start.querySelector('.bi').className = 'bi bi-mic-fill';
            els.tip.textContent = 'Catat dengan Suara';
            els.status.textContent = 'Tekan tombol mikrofon lalu bicarakan transaksi kamu.';
            els.stop.disabled = false;
        }

        function showError(message) {
            els.err.textContent = message;
            els.err.classList.remove('d-none');
        }

        function clearAudio() {
            audioBlob = null;
            if (audioURL) {
                URL.revokeObjectURL(audioURL);
                audioURL = null;
            }
            els.audio.removeAttribute('src');
            els.audioBox.classList.add('d-none');
        }

        function getToken() {
            if (root.dataset.csrf && !csrf) {
                csrf = root.dataset.csrf;
            }
            return csrf;
        }

        async function postForm(url, formData) {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getToken(), 'Accept': 'application/json' },
                body: formData,
            });
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Terjadi kesalahan. Coba lagi.');
            }
            return data;
        }

        function wordsToRaw() {
            return finalTranscript.trim();
        }

        async function handleRecognitionEnd() {
            if (!listening) {
                return;
            }
            listening = false;
            stopRecorder();
            els.wave.classList.add('d-none');
            els.start.classList.remove('listening');
            els.start.querySelector('.bi').className = 'bi bi-mic-fill';
            els.actions.classList.add('d-none');

            const transcript = (finalTranscript + ' ' + interimTranscript).trim();

            if (transcript.length < 3) {
                showError('Suara tidak dapat dikenali. Silakan ulangi atau masukkan transaksi secara manual.');
                return;
            }

            els.status.textContent = 'Memahami ucapan…';
            els.live.textContent = `"${transcript}"`;
            els.live.classList.remove('d-none');

            try {
                const formData = new FormData();
                formData.append('transcript', transcript);
                const data = await postForm(window.Eltrack?.routes['voice.parse'] ?? '/voice/parse', formData);
                parsePayload = data;
                renderPreview(data);
            } catch (e) {
                showError(e.message || 'Gagal memahami transaksi. Silakan ulangi.');
            }
        }

        function renderPreview(data) {
            const parsed = data.parsed;
            const amount = parsed.amount ?? '';
            els.quote.textContent = `"${wordsToRaw()}"`;
            els.stage.classList.add('d-none');
            els.preview.classList.remove('d-none');

            els.transcription.value = parsed.transcript || '';
            els.duration.value = Math.max(1, Math.round(audioBlob ? (audioBlob.size / 16000) : 0));

            els.date.value = new Date().toISOString().slice(0, 10);

            els.fieldType.value = parsed.type ?? 'expense';
            els.description.value = parsed.description || '';

            els.warnings.innerHTML = '';
            (parsed.warnings || []).forEach((w) => {
                const div = document.createElement('div');
                div.className = 'alert alert-warning py-2 mb-2';
                div.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>' + w;
                els.warnings.appendChild(div);
            });

            if (amount) {
                els.form.querySelector('[name="amount"]').value = new Intl.NumberFormat('id-ID').format(amount);
            } else {
                els.form.querySelector('[name="amount"]').value = '';
            }

            fillCategories(parsed.type ?? 'expense', data.category_id);

            els.account.innerHTML = '';
            (data.accounts || []).forEach((acc) => {
                const opt = document.createElement('option');
                opt.value = acc.id;
                opt.textContent = acc.name;
                if (acc.id === data.default_account_id) {
                    opt.selected = true;
                }
                els.account.appendChild(opt);
            });

            if (audioBlob) {
                if (audioURL) {
                    URL.revokeObjectURL(audioURL);
                }
                audioURL = URL.createObjectURL(audioBlob);
                els.audio.src = audioURL;
                els.audioBox.classList.remove('d-none');
            }
        }

        function fillCategories(type, selectedId) {
            els.category.innerHTML = '';
            (parsePayload.categories || []).filter((c) => c.type === type).forEach((cat) => {
                const opt = document.createElement('option');
                opt.value = cat.id;
                opt.textContent = cat.name;
                opt.dataset.icon = cat.icon;
                opt.dataset.color = cat.color;
                if (cat.id === selectedId) {
                    opt.selected = true;
                }
                els.category.appendChild(opt);
            });
        }

        function startRecognition() {
            if (!SpeechRecognition) {
                showError('Browser kamu belum mendukung input suara. Silakan gunakan Chrome/Edge versi terbaru atau gunakan pencatatan manual.');
                return;
            }
            try {
                recognition = new SpeechRecognition();
                recognition.lang = 'id-ID';
                recognition.continuous = true;
                recognition.interimResults = true;
                recognition.maxAlternatives = 1;

                recognition.onresult = (event) => {
                    interimTranscript = '';
                    for (let i = event.resultIndex; i < event.results.length; i++) {
                        const text = event.results[i][0].transcript;
                        if (event.results[i].isFinal) {
                            finalTranscript += text + ' ';
                        } else {
                            interimTranscript += text;
                        }
                    }
                    els.live.classList.remove('d-none');
                    els.live.textContent = `"${(finalTranscript + interimTranscript).trim()}"`;
                };

                recognition.onerror = (event) => {
                    if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                        showError('Tolong izinkan akses microphone untuk menggunakan fitur pencatatan suara.');
                    } else if (event.error === 'no-speech') {
                        showError('Tidak ada suara yang terdeteksi. Silakan coba lagi.');
                    } else if (event.error !== 'aborted') {
                        showError('Transkripsi gagal. Silakan ulangi atau masukkan transaksi secara manual.');
                    }
                };

                recognition.onend = () => {
                    handleRecognitionEnd();
                };

                finalTranscript = '';
                interimTranscript = '';
                listening = true;
                recognition.start();
            } catch (e) {
                showError('Gagal memulai pengenalan suara. Silakan coba lagi.');
            }
        }

        function startRecorder() {
            if (!navigator.mediaDevices || !window.MediaRecorder) {
                return;
            }
            navigator.mediaDevices.getUserMedia({ audio: true }).then((stream) => {
                const mime = MediaRecorder.isTypeSupported('audio/webm;codecs=opus')
                    ? 'audio/webm;codecs=opus'
                    : (MediaRecorder.isTypeSupported('audio/mp4') ? 'audio/mp4' : '');
                recorder = new MediaRecorder(stream, mime ? { mimeType: mime } : undefined);
                audioChunks = [];
                recorder.ondataavailable = (e) => {
                    if (e.data.size > 0) {
                        audioChunks.push(e.data);
                    }
                };
                recorder.onstop = () => {
                    stream.getTracks().forEach((t) => t.stop());
                    if (audioChunks.length > 0) {
                        audioBlob = new Blob(audioChunks, { type: recorder.mimeType || 'audio/webm' });
                    }
                };
                recorder.start();
            }).catch(() => {
                // Microphone permission ditolak — tetap lanjut dengan transkripsi bila tersedia.
            });
        }

        function stopRecorder() {
            if (recorder && recorder.state !== 'inactive') {
                recorder.stop();
            }
        }

        function stopRecognition() {
            if (recognition) {
                recognition.onend = null;
                try {
                    recognition.stop();
                } catch (e) {
                    // ignore
                }
            }
        }

        els.start.addEventListener('click', () => {
            els.err.classList.add('d-none');
            els.wave.classList.remove('d-none');
            els.actions.classList.remove('d-none');
            els.start.classList.add('listening');
            els.start.querySelector('.bi').className = 'bi bi-mic';
            els.tip.textContent = 'Mendengarkan…';
            els.status.textContent = 'Silakan bicara, contoh: "Bayar listrik 150 ribu"';
            startRecorder();
            startRecognition();
        });

        els.stop.addEventListener('click', () => {
            els.stop.disabled = true;
            try {
                recognition && recognition.stop();
            } catch (e) {
                // ignore
            }
        });

        els.cancel.addEventListener('click', () => {
            reset();
            const modal = root.closest('.modal');
            if (modal) {
                window.bootstrap && bootstrap.Modal.getInstance(modal)?.hide();
            }
        });

        els.retry.forEach((btn) => btn.addEventListener('click', reset));

        els.clearAudio.addEventListener('click', clearAudio);

        els.fieldType.addEventListener('change', () => {
            fillCategories(els.fieldType.value, null);
        });

        els.save.addEventListener('click', async () => {
            const amountRaw = els.form.querySelector('[name="amount"]').value;
            if (!amountRaw || parseInt(amountRaw.replace(/\D/g, ''), 10) <= 0) {
                els.saveErr.textContent = 'Nominal transaksi belum diisi. Silakan masukkan nominal.';
                els.saveErr.classList.remove('d-none');
                return;
            }

            els.saveErr.classList.add('d-none');
            els.save.disabled = true;
            els.save.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan…';

            try {
                const formData = new FormData(els.form);
                formData.set('amount', parseInt(amountRaw.replace(/\D/g, ''), 10));
                formData.set('source', 'voice');
                if (audioBlob) {
                    formData.append('audio', audioBlob, 'voice.webm');
                    formData.append('duration', Math.max(1, Math.round(audioBlob.size / 16000)));
                }

                const data = await postForm(window.Eltrack?.routes['voice.store'] ?? '/voice/transactions', formData);

                showToast('success', data.message || 'Transaksi berhasil dicatat.');
                const modal = root.closest('.modal');
                if (modal) {
                    window.bootstrap && bootstrap.Modal.getInstance(modal)?.hide();
                }
                clearAudio();
                reset();

                setTimeout(() => window.location.reload(), 600);
            } catch (e) {
                els.saveErr.textContent = e.message || 'Gagal menyimpan transaksi.';
                els.saveErr.classList.remove('d-none');
                els.save.disabled = false;
                els.save.innerHTML = '<i class="bi bi-check-lg me-1"></i> Simpan Transaksi';
            }
        });

        // Reset saat modal ditutup
        if (root.closest('.modal')) {
            root.closest('.modal').addEventListener('hidden.bs.modal', reset);
        }

        function showToast(type, message) {
            const icons = { success: 'bi-check-circle-fill text-success', error: 'bi-x-circle-fill text-danger' };
            const container = document.querySelector('.toast-ft') || createToastContainer();
            const toast = document.createElement('div');
            toast.className = 'toast show';
            toast.innerHTML = `<div class="toast-body d-flex align-items-center gap-2"><i class="bi ${icons[type] ?? icons.success} fs-5"></i><span class="flex-grow-1">${message}</span><button type="button" class="btn-close" data-bs-dismiss="toast"></button></div>`;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }

        function createToastContainer() {
            const div = document.createElement('div');
            div.className = 'toast-ft';
            document.body.appendChild(div);
            return div;
        }
    });
}