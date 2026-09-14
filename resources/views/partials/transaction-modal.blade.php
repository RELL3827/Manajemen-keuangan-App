<div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="transactionForm" novalidate>
                @csrf
                <input type="hidden" name="source" value="manual">
                <input type="hidden" name="_method" value="POST" id="txMethod">

                <div class="modal-header">
                    <h5 class="modal-title" id="txModalTitle">Tambah Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Jenis Transaksi</label>
                        <div class="btn-group w-100" id="txTypeGroup">
                            <button type="button" class="btn btn-ft-green" data-type="income" data-bs-toggle="button">
                                <i class="bi bi-plus-lg me-1"></i>Uang Masuk
                            </button>
                            <button type="button" class="btn btn-ft-red" data-type="expense" data-bs-toggle="button" autofocus>
                                <i class="bi bi-dash-lg me-1"></i>Uang Keluar
                            </button>
                        </div>
                        <input type="hidden" name="type" id="txType" value="expense">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nominal (Rp)</label>
                            <input type="text" class="form-control" name="amount" id="txAmount" inputmode="numeric" data-format-money placeholder="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" name="transaction_date" id="txDate" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Akun / Wallet</label>
                            <select class="form-select" name="account_id" id="txAccount" required>
                                @foreach($accounts ?? [] as $acc)
                                    <option value="{{ $acc->id }}" @selected($acc->is_default)>{{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Metode Pembayaran</label>
                            <select class="form-select" name="payment_method" id="txPayment" required>
                                @foreach(['Tunai', 'Transfer Bank', 'Kartu Debit', 'Kartu Kredit', 'E-Wallet', 'QRIS', 'Lainnya'] as $m)
                                    <option value="{{ $m }}" @selected($m === 'Tunai')>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="category_id" id="txCategory" required>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Deskripsi</label>
                            <input type="text" class="form-control" name="description" id="txDescription" maxlength="255" placeholder="cth: makan siang">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                            <textarea class="form-control" name="notes" id="txNotes" rows="2" maxlength="2000"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <span class="me-auto fs-xs text-danger" id="txFormError"></span>
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-ft">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>