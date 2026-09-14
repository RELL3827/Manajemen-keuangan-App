@props(['compact' => false])

<div class="voice-capture" data-voice-capture>
    <div class="vc-stage text-center py-3">
        <button type="button" class="ft-mic" data-vc-start aria-label="Mulai merekam">
            <i class="bi bi-mic-fill"></i>
        </button>
        <h5 class="mt-4 mb-1 fw-750" data-vc-tip>Catat dengan Suara</h5>
        <p class="text-muted fs-xs mb-0" data-vc-status>
            Tekan tombol mikrofon lalu bicarakan transaksi kamu.
        </p>

        <div class="ft-wave d-none" data-vc-wave>
            <span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>
        </div>

        <div class="ft-transcript mt-3 text-start d-none" data-vc-live></div>

        <div class="d-flex justify-content-center gap-2 mt-3 d-none" data-vc-actions>
            <button type="button" class="btn btn-ft-red" data-vc-stop>
                <i class="bi bi-stop-fill me-1"></i> Selesai
            </button>
            <button type="button" class="btn btn-ghost" data-vc-retry>
                <i class="bi bi-arrow-repeat me-1"></i> Ulangi
            </button>
        </div>

        <div class="alert alert-danger text-start mt-3 mb-0 d-none py-2" data-vc-err></div>
    </div>

    <div class="vc-preview d-none" data-vc-preview>
        <div class="border rounded-3 bg-light p-3 mb-3">
            <div class="fs-xs fw-700 text-uppercase text-muted mb-1">Hasil Voice Input</div>
            <div class="fw-650" data-vc-quote>""</div>
            <div class="d-none" data-vc-audio-box>
                <div class="d-flex align-items-center gap-2 mt-2">
                    <i class="bi bi-mic text-value"></i>
                    <audio data-vc-audio controls class="flex-grow-1" style="height:36px"></audio>
                    <button type="button" class="btn btn-sm btn-ghost" data-vc-clear-audio title="Hapus rekaman"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        </div>

        <div data-vc-warnings></div>
        <div class="alert alert-danger py-2 mb-2 d-none" data-vc-save-err></div>

        <form data-vc-form>
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label">Jenis</label>
                    <select class="form-select" name="type" data-vc-field-type>
                        <option value="expense">Uang Keluar</option>
                        <option value="income">Uang Masuk</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Nominal (Rp)</label>
                    <input type="text" class="form-control text-end fw-700" name="amount" data-format-money inputmode="numeric" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Kategori</label>
                    <select class="form-select" name="category_id" data-vc-category required></select>
                </div>
                <div class="col-6">
                    <label class="form-label">Akun / Dompet</label>
                    <select class="form-select" name="account_id" data-vc-account required></select>
                </div>
                <div class="col-6">
                    <label class="form-label">Tanggal</label>
                    <input type="date" class="form-control" name="transaction_date" data-vc-date required>
                </div>
                <div class="col-6">
                    <label class="form-label">Metode Pembayaran</label>
                    <select class="form-select" name="payment_method">
                        <option>Cash</option>
                        <option>Bank</option>
                        <option>E-Wallet</option>
                        <option>QRIS</option>
                        <option>Debit</option>
                        <option>Credit</option>
                        <option>Lainnya</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <input type="text" class="form-control" name="description" data-vc-description maxlength="255">
                </div>
                <div class="col-12">
                    <label class="form-label">Catatan</label>
                    <textarea class="form-control" name="notes" rows="2" maxlength="2000" data-vc-notes></textarea>
                </div>
                <input type="hidden" name="source" value="voice">
                <input type="hidden" name="transcription" data-vc-transcription>
                <input type="hidden" name="duration" data-vc-duration>
            </div>
        </form>

        <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="button" class="btn btn-ft-green flex-grow-1" data-vc-save>
                <i class="bi bi-check-lg me-1"></i> Simpan Transaksi
            </button>
            <button type="button" class="btn btn-ghost" data-vc-retry>
                <i class="bi bi-arrow-repeat me-1"></i> Ulangi
            </button>
            <button type="button" class="btn btn-ghost" data-vc-cancel>
                <i class="bi bi-x-lg me-1"></i> Batalkan
            </button>
        </div>
    </div>
</div>