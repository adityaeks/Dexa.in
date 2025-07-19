@php
    /** @var $record App\Models\Order */
@endphp


<style>
    .invoice-container {
        max-width: 700px;
        margin: 0 auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        padding: 40px 32px 32px 32px;
        font-family: 'Segoe UI', Arial, sans-serif;
        color: #222;
    }

    .invoice-header {
        text-align: center;
        margin-bottom: 24px;
    }

    .invoice-title {
        font-size: 2.2rem;
        font-weight: bold;
        letter-spacing: 2px;
        color: #2d3748;
    }

    .invoice-meta {
        width: 100%;
        margin-bottom: 24px;
        font-size: 1rem;
    }

    .invoice-meta td {
        padding: 4px 8px;
        vertical-align: top;
    }

    .invoice-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 24px;
    }

    .invoice-table th,
    .invoice-table td {
        border: 1px solid #e2e8f0;
        padding: 12px 10px;
        text-align: left;
    }

    .invoice-table th {
        background: #f7fafc;
        font-weight: 600;
        color: #2d3748;
    }

    .invoice-table tfoot td {
        font-weight: bold;
        background: #f1f5f9;
    }

    .invoice-note {
        margin-top: 32px;
        background: #f7fafc;
        border-left: 4px solid #3182ce;
        padding: 16px 20px;
        border-radius: 6px;
        color: #2d3748;
    }

    .invoice-footer {
        margin-top: 40px;
        text-align: right;
        font-size: 0.95rem;
        color: #888;
    }
</style>

<div class="invoice-container">
    <div class="invoice-header">
        <img src="{{ asset('images/logo.png') }}" alt="Dexa.in Logo" style="height:56px; margin-bottom:8px; display:block; margin-left:auto; margin-right:auto;">
        <div class="invoice-title" style="margin-top:0; margin-bottom:50px;">INVOICE ORDER {{ $record->nomer_nota }}</div>
    </div>
    <table class="invoice-meta">
        <tr>
            <td><strong>Customer:</strong> {{ $record->customer?->name ?? '-' }}</td>
            <td><strong>Tanggal:</strong> {{ $record->created_at?->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td><strong>Contact Custommer:</strong> {{ $record->customer?->nomor ?? '-' }}</td>
        </tr>
    </table>
    <table class="invoice-table">
        <thead>
            <tr>
                <th>Deskripsi</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $record->harga?->nama ?? '-' }}</td>
                <td>Rp {{ number_format((int) $record->price, 0, '', '.') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td style="text-align: right;">Total</td>
                <td>Rp {{ number_format((int) $record->price, 0, '', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    <div class="invoice-note">
        <strong>Informasi Pembayaran:</strong>
        <div style="padding-left:32px;">
            <table style="margin: 8px 0 0 0; font-size:1rem; border:none;">
                <tr>
                    <td style="min-width:160px;"><strong>DANA</strong> (A/N Amar)</td>
                    <td style="padding: 0 8px;">:</td>
                    <td><span style="font-family:monospace;">0896-1234-5678</span></td>
                </tr>
                <tr>
                    <td style="min-width:160px;"><strong>SeaBank</strong> (A/N Cece)</td>
                    <td style="padding: 0 8px;">:</td>
                    <td><span style="font-family:monospace;">1234567890</span></td>
                </tr>
            </table>
        </div>
        <div style="margin-top:16px;"></div>
        <strong>Note:</strong>
        <div>Harap melampirkan bukti pembayaran</div>
    </div>
    <div class="invoice-footer">
        Dicetak pada {{ now()->format('d-m-Y H:i') }} oleh Dexa.in
    </div>
</div>
